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

use pocketmine\scheduler\{TaskScheduler, ClosureTask};

class DoubleChestInventory extends SimpleInventory implements BlockInventory{
	
	protected Position $holder;
	
	private Block $block1;
	private Block $block2;
	
	public function __construct(private TaskScheduler $scheduler, Position $holder, private string $title){
		$this->holder = new Position((int) $holder->x, (int) $holder->y + 4, (int) $holder->z, $holder->world);
		parent::__construct(54);
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
		$this->sendBlocks($network, $chest, $x);
		$this->sendBlocks($network, $chest, $x1);
		$pk = BlockActorDataPacket::create(new BlockPosition($x,$y,$z),new CacheableNbt($nbt1));
		$network->sendDataPacket($pk);
		$pk = ContainerOpenPacket::blockInv($network->getInvManager()->getWindowId($this), 0, new BlockPosition($x,$y,$z));
		$this->scheduler->scheduleDelayedTask(new ClosureTask(function() use($pk, $network) : void{
			$network->sendDataPacket($pk);
			$this->setReady();
		}), 10);
	}
	
	public function onClose(Player $who) :void{
		parent::onClose($who);
		$x = $this->holder->x;
		$network = $who->getNetworkSession();
		$this->sendBlocks($network, $this->block1, $x);
		$this->sendBlocks($network, $this->block2, $x + 1);
	}
	
	public function getNetworkType() :int{
		return 0;
	}
	
	public function getName() :string{
		return $this->title;
	}
	
	public function getHolder() :Position{
		return $this->holder;
	}
	
	private function sendBlocks(NetworkSession $network, Block $block, int $x) :void{
		$pos = $this->holder;
		$pk = UpdateBlockPacket::create(
			new BlockPosition($x,$pos->y,$pos->z),
			RuntimeBlockMapping::getInstance()->toRuntimeId($block->getFullId()),
			UpdateBlockPacket::FLAG_NETWORK,
			UpdateBlockPacket::DATA_LAYER_NORMAL);
		$network->sendDataPacket($pk);
	}
	
}