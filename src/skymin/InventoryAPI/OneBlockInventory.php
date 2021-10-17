<?php
declare(strict_types = 1);

namespace skymin\InventoryAPI;

use pocketmine\player\Player;

use pocketmine\inventory\SimpleInventory;
use pocketmine\block\inventory\BlockInventory;

use pocketmine\block\{Block, BlockFactory};

use pocketmine\world\Position;

use pocketmine\network\mcpe\protocol\{
	ContainerOpenPacket,
	BlockActorDataPacket,
	UpdateBlockPacket
};

use pocketmine\network\mcpe\protocol\types\CacheableNbt;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\convert\RuntimeBlockMapping;

class OneBlockInventory extends SimpleInventory implements BlockInventory{
	
	protected Position $holder;
	
	private Block $block;
	
	private int $size;
	
	public function __construct(Position $holder, private int $blockId, private int $windowType, int $size, private string $title){
		$this->holder = new Position((int) $holder->x, (int) $holder->y + 4, (int) $holder->z, $holder->world);
		$this->size = $size;
		parent::__construct($size);
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
		$this->sendBlocks($who, $block);
		$nbt = CompoundTag::create()->setString('CustomName', $this->title);
		$pk = BlockActorDataPacket::create($x, $y, $z, new CacheableNbt($nbt));
		$network->sendDataPacket($pk);
		$pk = ContainerOpenPacket::blockInv($network->getInvManager()->getWindowId($this), $this->windowType, $x, $y, $z);
		$network->sendDataPacket($pk);
	}
	
	public function onClose(Player $who) :void{
		parent::onClose($who);
		$this->sendBlocks($who, $this->block);
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
	
	private function sendBlocks(Player $player, Block $block) :void{
		$pos = $this->holder;
		$pk = UpdateBlockPacket::create($pos->x, $pos->y, $pos->z, RuntimeBlockMapping::getInstance()->toRuntimeId($block->getFullId()));
		$player->getNetworkSession()->sendDataPacket($pk);
	}
	
}