<?php
declare(strict_types = 1);

namespace skymin\InventoryLib;

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

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\convert\RuntimeBlockMapping;

use pocketmine\network\mcpe\NetworkSession;

class OneBlockInventory extends SimpleInventory implements BlockInventory{
	
	//window types
	public const TYPE_CHEST = 0;
	public const TYPE_DROPPER = 6;
	public const TYPE_HOPPER = 8;
	
	protected Position $holder;
	
	private Block $block;
	
	private int $blockId;
	
	private int $windowType;
	
	public function __construct(Position $holder, int $windowType, protected string $title){
		$this->holder = new Position((int) $holder->x, (int) $holder->y + 4, (int) $holder->z, $holder->world);
		$this->windowType = $windowType;
		if($windowType === self::TYPE_CHEST){
			parent::__construct(27);
			$this->blockId = 54;
		}elseif($windowType === TYPE_DROPPER){
			parent::__construct(9);
			$this->blockId = 125;
		}elseif($windowType === TYPE_HOPPER){
			parent::__construct(5);
			$this->blockId = 154;
		}else{
			parent::__construct(27);
			$this->blockId = 54;
		}
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
		$this->sendBlocks($network, $block);
		$nbt = CompoundTag::create()->setString('CustomName', $this->title);
		$pk = BlockActorDataPacket::create(new BlockPosition($x,$y,$z), new CacheableNbt($nbt));
		$network->sendDataPacket($pk);
		$pk = ContainerOpenPacket::blockInv(
			$network->getInvManager()->getWindowId($this),
			$this->windowType,
			new BlockPosition($x,$y,$z)
		);
		$network->sendDataPacket($pk);
	}
	
	public function onClose(Player $who) :void{
		parent::onClose($who);
		$this->sendBlocks($who->getNetworkSession(), $this->block);
	}
	
	public function getNetworkType() :int{
		return $this->windowType;
	}
	
	public function getName() :string{
		return $this->title;
	}
	
	public function getHolder() :Position{
		return $this->holder;
	}
	
	private function sendBlocks(NetworkSession $network, Block $block) :void{
		$pos = $this->holder;
		$pk = UpdateBlockPacket::create(
			new BlockPosition($pos->x,$pos->y,$pos->z),
			RuntimeBlockMapping::getInstance()->toRuntimeId($block->getFullId()),
			UpdateBlockPacket::FLAG_NETWORK,
			UpdateBlockPacket::DATA_LAYER_NORMAL);
		$network->sendDataPacket($pk);
	}
	
}