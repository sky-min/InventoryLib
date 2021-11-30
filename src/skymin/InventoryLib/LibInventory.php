<?php
declare(strict_types = 1);

namespace skymin\InventoryLib;

use pocketmine\Server;

use pocketmine\player\Player;

use pocketmine\item\Item;

use pocketmine\inventory\SimpleInventory;
use pocketmine\block\inventory\BlockInventory;

use pocketmine\block\{Block, BlockFactory};

use pocketmine\world\Position;

use pocketmine\network\mcpe\protocol\{
	ContainerOpenPacket,
	BlockActorDataPacket,
	UpdateBlockPacket
};

use pocketmine\network\mcpe\compression\ZlibCompressor;
use pocketmine\network\mcpe\protocol\serializer\{PacketSerializerContext, PacketBatch};
use pocketmine\network\mcpe\convert\{RuntimeBlockMapping, GlobalItemTypeDictionary};

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\{CacheableNbt, BlockPosition};

use pocketmine\network\mcpe\NetworkSession;

use pocketmine\scheduler\ClosureTask;

class LibInventory extends SimpleInventory implements BlockInventory{
	
	private Block $block1;
	private ?Block $block2 = null;
	
	public function __construct(private InvInfo $info){
		parent::__construct($this->info->size);
	}
	
	protected function onTransaction(Player $player,int $slot, Item $sourceItem, Item $targetItem) :bool{
		return true;
	}
	
	public function onOpen(Player $who) :void{
		parent::onOpen($who);
		$info = $this->info;
		$network = $who->getNetworkSession();
		$holder = $info->holder;
		$x = $holder->x;
		$y = $holder->y;
		$z = $holder->z;
		$world = $holder->world;
		$block = BlockFactory::getInstance()->get($info->blockId, 0);
		$this->block1 = $world->getBlockAt($x, $y, $z);
		$nbt = CompoundTag::create()
			->setString('id', 'Chest')
			->setInt('Chest', 1)
			->setString('CustomName', $info->title)
			->setInt('x', $x)
			->setInt('y', $y)
			->setInt('z', $z);
		$packets = array();
		if($info->isDouble()){
			$x2 = $x + 1;
			$this->block2 = $world->getBlockAt($x2, $y, $z);
			$nbt->setInt('pairx', $x2)->setInt('pairz', $z);
			$packets[] = UpdateBlockPacket::create(
				new BlockPosition($x2,$y,$z),
				RuntimeBlockMapping::getInstance()->toRuntimeId($block->getFullId()),
				UpdateBlockPacket::FLAG_NETWORK,
				UpdateBlockPacket::DATA_LAYER_NORMAL
			);
		}
		$packets[] = UpdateBlockPacket::create(
			new BlockPosition($x,$y,$z),
			RuntimeBlockMapping::getInstance()->toRuntimeId($block->getFullId()),
			UpdateBlockPacket::FLAG_NETWORK,
			UpdateBlockPacket::DATA_LAYER_NORMAL
		);
		$packets[] = BlockActorDataPacket::create(new BlockPosition($x,$y,$z), new CacheableNbt($nbt));
		$batch = Server::getInstance()->prepareBatch(PacketBatch::fromPackets(new PacketSerializerContext(GlobalItemTypeDictionary::getInstance()->getDictionary()), ...$packets), ZlibCompressor::getInstance());
		$network->queueCompressed($batch);
		$pk = ContainerOpenPacket::blockInv(
			$network->getInvManager()->getWindowId($this),
			$info->windowType,
			new BlockPosition($x,$y,$z)
		);
		InvLibManager::$register->getScheduler()->scheduleDelayedTask(new ClosureTask(function() use($pk, $network) :void{
			$network->sendDataPacket($pk);
			$this->setContents($this->getContents());
		}), 7);
	}
	
	public function onClose(Player $who) :void{
		parent::onClose($who);
		$network = $who->getNetworkSession();
		$holder = $this->info->holder;
		$x = $holder->x;
		$y = $holder->y;
		$z = $holder->z;
		$pk1 = UpdateBlockPacket::create(
			new BlockPosition($holder->x,$holder->y,$holder->z),
			RuntimeBlockMapping::getInstance()->toRuntimeId($this->block1->getFullId()),
			UpdateBlockPacket::FLAG_NETWORK,
			UpdateBlockPacket::DATA_LAYER_NORMAL
		);
		if($this->block2 === null){
			$network->sendDataPacket($pk1);
			return;
		}
		$pk2 = UpdateBlockPacket::create(
			new BlockPosition($x + 1,$y,$z),
			RuntimeBlockMapping::getInstance()->toRuntimeId($this->block2->getFullId()),
			UpdateBlockPacket::FLAG_NETWORK,
			UpdateBlockPacket::DATA_LAYER_NORMAL
		);
		$batch = Server::getInstance()->prepareBatch(PacketBatch::fromPackets(new PacketSerializerContext(GlobalItemTypeDictionary::getInstance()->getDictionary()), $pk1, $pk2), ZlibCompressor::getInstance());
		$network->queueCompressed($batch);
	}
	
	final public function getName() :string{
		return $this->info->title;
	}
	
	final public function getHolder() :Position{
		return $this->info->holder;
	}
	
	final public function send(Player $player) :void{
		$player->setCurrentWindow($this);
	}
	
	final public function close(Player $player) :void{
		$this->onClose($player);
	}
	
}