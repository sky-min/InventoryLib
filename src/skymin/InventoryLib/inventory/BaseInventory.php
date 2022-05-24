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

namespace skymin\InventoryLib\inventory;

use skymin\InventoryLib\InvLibHandler;
use skymin\InventoryLib\action\InventoryAction;

use pocketmine\player\Player;
use pocketmine\inventory\SimpleInventory;
use pocketmine\block\inventory\{BlockInventory, BlockInventoryTrait};

use pocketmine\world\Position;
use pocketmine\block\{Block, BlockFactory};

use pocketmine\network\mcpe\protocol\{
	ContainerOpenPacket,
	BlockActorDataPacket,
	UpdateBlockPacket
};

use pocketmine\scheduler\ClosureTask;
use pocketmine\event\inventory\InventoryOpenEvent;

use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\convert\RuntimeBlockMapping;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\block\tile\Spawnable;
use pocketmine\network\mcpe\protocol\types\{CacheableNbt, BlockPosition};

use function spl_object_id;

abstract class BaseInventory extends SimpleInventory implements BlockInventory{
	use BlockInventoryTrait;

	final protected static function sendBlock(BlockPosition $pos, NetworkSession $network, int $blockId) :void{
		$pk = UpdateBlockPacket::create(
			$pos,
			RuntimeBlockMapping::getInstance()->toRuntimeId($blockId),
			UpdateBlockPacket::FLAG_NETWORK,
			UpdateBlockPacket::DATA_LAYER_NORMAL
		);
		$network->sendDataPacket($pk);
	}

	public function __construct(private InvType $type, private string $title = ''){
		parent::__construct($this->type->getSize());
		if(InvLibHandler::getScheduler() === null){
			throw new \LogicException('Tried creating inventory before calling ' . InvLibHandler::class . 'register');
		}
	}

	final public function send(Player $player) : void{
		$pos = $player->getPosition();
		$y = $pos->y;
		if($y - 2 > -64 && $y - 2 < 256){
			$y -= 2;
		}elseif($y + 3 > -64 && $y + 3 < 256){
			$y += 3;
		}else{
			return;
		}
		$this->holder = $holder = new Position(
			(int) $pos->x,
			(int) $y,
			(int) $pos->z, $pos->world
		);
		// Send FakeBlock
		$type = $this->type;
		$network = $player->getNetworkSession();
		$x = $holder->x;
		$y = $holder->y;
		$z = $holder->z;
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
			self::sendBlock(new BlockPosition($x2, $y, $z), $network, $blockId);
		}
		$blockpos = new BlockPosition($x, $y, $z);
		self::sendBlock($blockpos, $network, $blockId);
		$pk = BlockActorDataPacket::create($blockpos, new CacheableNbt($nbt));
		$network->sendDataPacket($pk);
		//Player::setCurrentWindow only applies to Chest...
		InvLibHandler::getScheduler()->scheduleDelayedTask(new ClosureTask(function() use($player, $network, $type, $blockpos) : void{
			if(!$network->isConnected()) return;
			$ev = new InventoryOpenEvent($this, $player);
			$ev->call();
			if($ev->isCancelled()){
				$this->sendRealBlock($player);
				return;
			}
			$player->removeCurrentWindow();
			$inventoryManager = $network->getInvManager();
			$pk = ContainerOpenPacket::blockInv(
				(fn(BaseInventory $inv) => $this->addDynamic($inv))->call($inventoryManager, $this),
				$type->getWindowType(),
				$blockpos
			);
			$network->sendDataPacket($pk);
			$inventoryManager->syncContents($this);
			$this->onOpen($player);
			(fn(BaseInventory $inv) => $this->currentWindow = $inv)->call($player, $this);
		}), 8);
	}

	public function onClose(Player $who) : void{
		parent::onClose($who);
		$this->sendRealBlock($who);
	}

	final protected function sendRealBlock(Player $player) : void{
		$network = $player->getNetworkSession();
		$holder = $this->holder;
		$x = $holder->x;
		$y = $holder->y;
		$z = $holder->z;
		$world = $holder->world;
		$blockpos = new BlockPosition($x,$y,$z);
		$block = $world->getBlockAt($x, $y, $z);
		self::sendBlock($blockpos, $network, $block->getFullId());
		$tile = $world->getTileAt($x, $y, $z);
		if($tile instanceof Spawnable){
			$pk = BlockActorDataPacket::create($blockpos, $tile->getSerializedSpawnCompound());
			$network->sendDataPacket($pk);
		}
		if($this->type->isDouble()){
			$x += 1;
			$block = $world->getBlockAt($x, $y, $z);
			$blockpos = new BlockPosition($x,$y,$z);
			self::sendBlock($blockpos, $network, $block->getFullId());
			$tile = $world->getTileAt($x, $y, $z);
			if($tile instanceof Spawnable){
				$pk = BlockActorDataPacket::create($blockpos, $tile->getSerializedSpawnCompound());
				$network->sendDataPacket($pk);
			}
		}
	}

	// If it returns false, the event is canceled.
	public function onAction(InventoryAction $action) : bool{}

	// Player::removeCurrentWindow() does not work with the next Window.
	final public function close(Player $player) : void{ //
		$this->onClose($player);
		(fn() => $this->currentWindow = null)->call($player);
	}

	final public function getTitle() : string{
		return $this->title;
	}

	final public function setTitle(string $title) : void{
		$this->title = $title;
	}

	final public function getTypeInfo() : InvType{
		return $this->type;
	}

}