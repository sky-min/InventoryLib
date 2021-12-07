<?php
/**
 *      _                    _       
 *  ___| | ___   _ _ __ ___ (_)_ __  
 * / __| |/ / | | | '_ ` _ \| | '_ \ 
 * \__ \   <| |_| | | | | | | | | | |
 * |___/_|\_\\__, |_| |_| |_|_|_| |_|
 *           |___/ 
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the MIT License. see <https://opensource.org/licenses/MIT>.
 * 
 * @author skymin
 * @link   https://github.com/sky-min
 * @license https://opensource.org/licenses/MIT MIT License
 * 
 *   /\___/\
 * 　(∩`・ω・)
 * ＿/_ミつ/￣￣￣/
 * 　　＼/＿＿＿/
 *
 */

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
	UpdateBlockPacket
};

use pocketmine\network\mcpe\compression\ZlibCompressor;
use pocketmine\network\mcpe\protocol\serializer\{PacketSerializerContext, PacketBatch};
use pocketmine\network\mcpe\convert\{RuntimeBlockMapping, GlobalItemTypeDictionary};

use pocketmine\network\mcpe\NetworkSession;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\block\tile\Spawnable;
use pocketmine\network\mcpe\protocol\types\{CacheableNbt, BlockPosition};

use pocketmine\scheduler\ClosureTask;

use pocketmine\utils\Utils;

use Closure;

use const null;

class LibInventory extends SimpleInventory implements BlockInventory{
	
	private ?Closure $listener = null;
	private ?Closure $closeListener = null;
	
	private Position $holder;
	
	public function __construct(private LibInvType $type, Position $holder, private string $title = ''){
		parent::__construct($this->type->getSize());
		if(InvLibManager::getScheduler() === null){
			throw new \LogicException('Tried creating menu before calling ' . InvLibManager::class . register);
		}
		$this->holder = new Position((int) $holder->x, (int) $holder->y, (int) $holder->z, $holder->world);
	}
	
	final public function send(Player $player, ?Closure $closure = null) :void{
		$player->setCurrentWindow($this);
		if($closure !== null){
			Utils::validateCallableSignature(function(Player $player) :void{}, $closure);
			($closure)($player);
		}
	}
	
	final public function close(Player $player, ?Closure $closure = null) :void{
		$this->onClose($player);
		if($closure !== null){
			Utils::validateCallableSignature(function(Player $player) :void{}, $closure);
			($closure)($player);
		}
	}
	
	final public function setListener(?Closure $closure = null) :void{
		Utils::validateCallableSignature(function(InvLibAction $action) :void{}, $closure);
		$this->listener = $closure;
	}
	
	final public function setCloseListener(?Closure $closure = null) :void{
		Utils::validateCallableSignature(function(Player $player) :void{}, $closure);
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
		$type = $this->type;
		$network = $who->getNetworkSession();
		$holder = $this->holder;
		$x = $holder->x;
		$y = $holder->y;
		$z = $holder->z;
		$world = $holder->world;
		$blockId = BlockFactory::getInstance()->get($type->getBlockId(), 0)->getFullId();
		$nbt = CompoundTag::create()
			->setString('id', 'Chest')
			->setInt('Chest', 1)
			->setString('CustomName', $this->title)
			->setInt('x', $x)
			->setInt('y', $y)
			->setInt('z', $z);
		if($type->isDouble()){
			$x2 = $x + 1;
			$nbt->setInt('pairx', $x2)->setInt('pairz', $z);
			$this->sendBlock($x2, $y, $z, $network, $blockId);
		}
		$this->sendBlock($x, $y, $z, $network, $blockId);
		$pk = BlockActorDataPacket::create(new BlockPosition($x,$y,$z), new CacheableNbt($nbt));
		$network->sendDataPacket($pk);
		$pk = ContainerOpenPacket::blockInv(
			$network->getInvManager()->getWindowId($this),
			$type->getWindowType(),
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
		$this->sendBlock($x, $y, $z, $network, $block->getFullId());
		$tile = $world->getTileAt($x, $y, $z);
		if($tile instanceof Spawnable){
			$pk = BlockActorDataPacket::create(new BlockPosition($x,$y,$z), $tile->getSerializedSpawnCompound());
			$network->sendDataPacket($pk);
		}
		if($this->type->isDouble()){
			$x += 1;
			$block = $world->getBlockAt($x, $y, $z);
			$this->sendBlock($x, $y, $z, $network, $block->getFullId());
			$tile = $world->getTileAt($x, $y, $z);
			if($tile instanceof Spawnable){
				$pk = BlockActorDataPacket::create(new BlockPosition($x,$y,$z), $tile->getSerializedSpawnCompound());
				$network->sendDataPacket($pk);
			}
		}
		if($this->closeListener !== null){
			($this->closeListener)($who);
		}
	}
	
	final public function getTitle() :string{
		return $this->title;
	}
	
	final public function getTypeInfo() :LibInvType{
		return $this->type;
	}
	
	final public function getHolder() :Position{
		return $this->holder;
	}
	
	private function sendBlock(int $x, int $y, int $z, NetworkSession $network, int $blockId) :void{
		$pk = UpdateBlockPacket::create(
			new BlockPosition($x, $y, $z),
			RuntimeBlockMapping::getInstance()->toRuntimeId($blockId),
			UpdateBlockPacket::FLAG_NETWORK,
			UpdateBlockPacket::DATA_LAYER_NORMAL
		);
		$network->sendDataPacket($pk);
	}
	
}