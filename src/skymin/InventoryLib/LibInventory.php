<?php
declare(strict_types = 1);

namespace skymin\InventoryLib;

use pocketmine\player\Player;

use pocketmine\inventory\SimpleInventory;
use pocketmine\block\inventory\BlockInventory;

use pocketmine\block\BlockFactory;

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

use pocketmine\scheduler\ClosureTask;

use Closure;

use const null;

class LibInventory extends SimpleInventory implements BlockInventory{
	
	private ?Closure $listener = null;
	private ?Closure $closeListener = null;
	
	private Position $holder;
	
	public function __construct(private InvInfo $info, Position $holder, private string $title = ''){
		parent::__construct($this->info->getSize());
		if(InvLibManager::getScheduler() === null){
			throw new LogicException('Tried creating menu before calling ' . InvLibManager::class . register);
		}
		$this->holder = new Position((int) $holder->x, (int) $holder->y, (int) $holder->z, $holder->world);
	}
	
	final public function send(Player $player, ?Closure $closure = null) :void{
		$player->setCurrentWindow($this);
		if($closure !== null){
			($closure)();
		}
	}
	
	final public function close(Player $player, ?Closure $closure = null) :void{
		$this->onClose($player);
		if($closure !== null){
			($closure)();
		}
	}
	
	final public function setListener(?Closure $closure = null) :void{
		$this->listener = $closure;
	}
	
	final public function setCloseListener(?Closure $closure = null) :void{
		$this->closeListener = $closure;
	}
	
	protected function onTransaction(InvLibAction $action) :void{}
	
	final protected function onActionSenssor(InvLibAction $action) :bool{
		$this->onTransaction($action);
		if($this->listener !== null){
			($this->listener)($action);
		}
		return $action->isCancelled();
	}
	
	public function onOpen(Player $who) :void{
		parent::onOpen($who);
		$info = $this->info;
		$network = $who->getNetworkSession();
		$holder = $this->holder;
		$x = $holder->x;
		$y = $holder->y;
		$z = $holder->z;
		$world = $holder->world;
		$block = BlockFactory::getInstance()->get($info->getBlockId(), 0);
		$nbt = CompoundTag::create()
			->setString('id', 'Chest')
			->setInt('Chest', 1)
			->setString('CustomName', $this->title)
			->setInt('x', $x)
			->setInt('y', $y)
			->setInt('z', $z);
		if($info->isDouble()){
			$x2 = $x + 1;
			$nbt->setInt('pairx', $x2)->setInt('pairz', $z);
			$pk = UpdateBlockPacket::create(
				new BlockPosition($x2,$y,$z),
				RuntimeBlockMapping::getInstance()->toRuntimeId($block->getFullId()),
				UpdateBlockPacket::FLAG_NETWORK,
				UpdateBlockPacket::DATA_LAYER_NORMAL
			);
			$network->sendDataPacket($pk);
		}
		$pk = UpdateBlockPacket::create(
			new BlockPosition($x,$y,$z),
			RuntimeBlockMapping::getInstance()->toRuntimeId($block->getFullId()),
			UpdateBlockPacket::FLAG_NETWORK,
			UpdateBlockPacket::DATA_LAYER_NORMAL
		);
		$network->sendDataPacket($pk);
		$pk = BlockActorDataPacket::create(new BlockPosition($x,$y,$z), new CacheableNbt($nbt));
		$network->sendDataPacket($pk);
		$pk = ContainerOpenPacket::blockInv(
			$network->getInvManager()->getWindowId($this),
			$info->getWindowType(),
			new BlockPosition($x,$y,$z)
		);
		InvLibManager::getScheduler()->scheduleDelayedTask(new ClosureTask(function() use($pk, $network) :void{
			$network->sendDataPacket($pk);
			$this->setContents($this->getContents());
		}), 10);
	}
	
	public function onClose(Player $who) :void{
		parent::onClose($who);
		$network = $who->getNetworkSession();
		$holder = $this->holder;
		$x = $holder->x;
		$y = $holder->y;
		$z = $holder->z;
		$world = $holder->world;
		$block = $world->getBlockAt($x, $y, $z);
		$pk = UpdateBlockPacket::create(
			new BlockPosition($x,$y,$z),
			RuntimeBlockMapping::getInstance()->toRuntimeId($block->getFullId()),
			UpdateBlockPacket::FLAG_NETWORK,
			UpdateBlockPacket::DATA_LAYER_NORMAL
		);
		$network->sendDataPacket($pk);
		if($this->info->isDouble()){
			$x += 1;
			$block = $world->getBlockAt($x, $y, $z);
			$pk = UpdateBlockPacket::create(
				new BlockPosition($x,$y,$z),
				RuntimeBlockMapping::getInstance()->toRuntimeId($block->getFullId()),
				UpdateBlockPacket::FLAG_NETWORK,
				UpdateBlockPacket::DATA_LAYER_NORMAL
			);
			$network->sendDataPacket($pk);
		}
		if($this->closeListener !== null){
			($this->closeListener)();
		}
	}
	
	final public function getTitle() :string{
		return $this->title;
	}
	
	final public function getInfo() :InvInfo{
		return $this->info;
	}
	
	final public function getHolder() :Position{
		return $this->holder;
	}
	
}