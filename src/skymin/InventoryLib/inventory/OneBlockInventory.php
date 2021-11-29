<?php
declare(strict_types = 1);

namespace skymin\InventoryLib\inventory;

use pocketmine\Server;

use pocketmine\player\Player;

use pocketmine\inventory\SimpleInventory;
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

use skymin\InventoryLib\util\EventTrait;

use function match;

class OneBlockInventory extends LibInventory implements BlockInventory{
	use EventTrait;
	
	//window types
	public const TYPE_CHEST = 0;
	public const TYPE_DROPPER = 6;
	public const TYPE_HOPPER = 8;
	
	private Block $block;
	
	private int $blockId;
	
	private int $windowType;
	
	public function __construct(Position $holder, int $windowType, string $title){
		$holder = new Position((int) $holder->x, (int) $holder->y + 4, (int) $holder->z, $holder->world);
		$this->windowType = $windowType;
		$size = match($windowType){
			self::TYPE_CHEST => 27,
			self::TYPE_DROPPER => 9,
			self::TYPE_HOPPER => 5,
			default => 27
		};
		parent::__construct($holder, $title,$size);
		$this->blockId = match($windowType){
			self::TYPE_CHEST => 54,
			self::TYPE_DROPPER => 125,
			self::TYPE_HOPPER => 154,
			default => 54
		};
	}
	
	public function onOpen(Player $who) :void{
		parent::onOpen($who);
		$network = $who->getNetworkSession();
		$holder = $this->holder;
		$x = $holder->x;
		$y = $holder->y;
		$z = $holder->z;
		$world = $holder->world;
		$this->block = $world->getBlockAt($x, $y, $z);
		$block = BlockFactory::getInstance()->get($this->blockId, 0);
		$nbt = CompoundTag::create()->setString('CustomName', $this->title);
		$pk1 = UpdateBlockPacket::create(
			new BlockPosition($x,$y,$z),
			RuntimeBlockMapping::getInstance()->toRuntimeId($block->getFullId()),
			UpdateBlockPacket::FLAG_NETWORK,
			UpdateBlockPacket::DATA_LAYER_NORMAL
		);
		$pk2 = BlockActorDataPacket::create(new BlockPosition($x,$y,$z), new CacheableNbt($nbt));
		$batch = Server::getInstance()->prepareBatch(PacketBatch::fromPackets(new PacketSerializerContext(GlobalItemTypeDictionary::getInstance()->getDictionary()), $pk1, $pk2), ZlibCompressor::getInstance());
		$network->queueCompressed($batch);
		$pk = ContainerOpenPacket::blockInv(
			$network->getInvManager()->getWindowId($this),
			$this->windowType,
			new BlockPosition($x,$y,$z)
		);
		$network->sendDataPacket($pk);
	}
	
	public function onClose(Player $who) :void{
		parent::onClose($who);
		$holder = $this->holder;
		$pk1 = UpdateBlockPacket::create(
			new BlockPosition($holder->x,$holder->y,$holder->z),
			RuntimeBlockMapping::getInstance()->toRuntimeId($this->block1->getFullId()),
			UpdateBlockPacket::FLAG_NETWORK,
			UpdateBlockPacket::DATA_LAYER_NORMAL
		);
		$who->getNetworkSession()->sendDataPacket($pk);
	}
	
}