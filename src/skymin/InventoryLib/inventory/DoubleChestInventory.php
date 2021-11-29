<?php
declare(strict_types = 1);

namespace skymin\InventoryLib\inventory;

use pocketmine\Server;

use pocketmine\player\Player;

use pocketmine\block\inventory\BlockInventory;

use pocketmine\block\{Block, BlockFactory};

use pocketmine\world\Position;

use pocketmine\network\mcpe\protocol\{
	ContainerOpenPacket,
	BlockActorDataPacket,
	types\BlockPosition,
	UpdateBlockPacket
};

use pocketmine\network\mcpe\protocol\types\CacheableNbt;

use pocketmine\network\mcpe\compression\ZlibCompressor;
use pocketmine\network\mcpe\protocol\serializer\{PacketSerializerContext, PacketBatch};
use pocketmine\network\mcpe\convert\GlobalItemTypeDictionary;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\convert\RuntimeBlockMapping;

use pocketmine\network\mcpe\NetworkSession;

use pocketmine\scheduler\ClosureTask;

use skymin\InventoryLib\InventoryLib;
use skymin\InventoryLib\util\EventTrait;

class DoubleChestInventory extends LibInventory implements BlockInventory{
	use EventTrait;
	
	protected Position $holder;
	
	private Block $block1;
	private Block $block2;
	
	public function __construct(Position $holder, string $title){
		$holder = new Position((int) $holder->x, (int) $holder->y + 4, (int) $holder->z, $holder->world);
		parent::__construct($holder, $title, 54);
	}
	
	protected function setReady() :void{
		
	}
	
	public function onOpen(Player $who) :void{
		parent::onOpen($who);
		$network = $who->getNetworkSession();
		$holder = $this->holder;
		$x = $holder->x;
		$y = $holder->y;
		$z = $holder->z;
		$world = $holder->world;
		$x1 = $x + 1;
		$this->block1 = $world->getBlockAt($x, $y, $z);
		$this->block2 = $world->getBlockAt($x1, $y, $z);
		$nbt1 = CompoundTag::create()
			->setString('id', 'Chest')
			->setInt('Chest', 1)
			->setString('CustomName', $this->title)
			->setInt('x', $x)
			->setInt('y', $y)
			->setInt('z', $z)
			->setInt('pairx', $x1)
			->setInt('pairz', $z);
		$chest = BlockFactory::getInstance()->get(54, 0);
		$packets = array();
		$pk1 = UpdateBlockPacket::create(
			new BlockPosition($x,$y,$z),
			RuntimeBlockMapping::getInstance()->toRuntimeId($chest->getFullId()),
			UpdateBlockPacket::FLAG_NETWORK,
			UpdateBlockPacket::DATA_LAYER_NORMAL
		);
		$pk2 = UpdateBlockPacket::create(
			new BlockPosition($x1,$y,$z),
			RuntimeBlockMapping::getInstance()->toRuntimeId($chest->getFullId()),
			UpdateBlockPacket::FLAG_NETWORK,
			UpdateBlockPacket::DATA_LAYER_NORMAL
		);
		$pk3 = BlockActorDataPacket::create(new BlockPosition($x,$y,$z),new CacheableNbt($nbt1));
		$batch = Server::getInstance()->prepareBatch(PacketBatch::fromPackets(new PacketSerializerContext(GlobalItemTypeDictionary::getInstance()->getDictionary()), $pk1, $pk2, $pk3), ZlibCompressor::getInstance());
		$network->queueCompressed($batch);
		$pk = ContainerOpenPacket::blockInv($network->getInvManager()->getWindowId($this), 0, new BlockPosition($x,$y,$z));
		InventoryLib::$register->getScheduler()->scheduleDelayedTask(new ClosureTask(function() use($pk, $network) :void{
			$network->sendDataPacket($pk);
			$this->setReady();
		}), 7);
	}
	
	public function onClose(Player $who) :void{
		parent::onClose($who);
		$holder = $this->holder;
		$x = $holder->x;
		$y = $holder->y;
		$z = $holder->z;
		$pk1 = UpdateBlockPacket::create(
			new BlockPosition($x,$y,$z),
			RuntimeBlockMapping::getInstance()->toRuntimeId($this->block1->getFullId()),
			UpdateBlockPacket::FLAG_NETWORK,
			UpdateBlockPacket::DATA_LAYER_NORMAL
		);
		$pk2 = UpdateBlockPacket::create(
			new BlockPosition($x + 1,$y,$z),
			RuntimeBlockMapping::getInstance()->toRuntimeId($this->block2->getFullId()),
			UpdateBlockPacket::FLAG_NETWORK,
			UpdateBlockPacket::DATA_LAYER_NORMAL
		);
		$batch = Server::getInstance()->prepareBatch(PacketBatch::fromPackets(new PacketSerializerContext(GlobalItemTypeDictionary::getInstance()->getDictionary()), $pk1, $pk2), ZlibCompressor::getInstance());
		$who->getNetworkSession()->queueCompressed($batch);
	}
	
}