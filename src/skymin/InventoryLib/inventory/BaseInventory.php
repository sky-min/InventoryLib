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
use skymin\InventoryLib\action\InvAction;

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

use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\convert\RuntimeBlockMapping;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\block\tile\Spawnable;
use pocketmine\network\mcpe\protocol\types\{CacheableNbt, BlockPosition};

use pocketmine\scheduler\ClosureTask;

abstract class BaseInventory extends SimpleInventory implements BlockInventory{
	use BlockInventoryTrait;

	protected static function sendBlock(int $x, int $y, int $z, NetworkSession $network, int $blockId) :void{
		$pk = UpdateBlockPacket::create(
			new BlockPosition($x, $y, $z),
			RuntimeBlockMapping::getInstance()->toRuntimeId($blockId),
			UpdateBlockPacket::FLAG_NETWORK,
			UpdateBlockPacket::DATA_LAYER_NORMAL
		);
		$network->addToSendBuffer($pk);
	}

	public function __construct(private LibInvType $type, private string $title = ''){
		parent::__construct($this->type->getSize());
		if(InvLibManager::getScheduler() === null){
			throw new \LogicException('Tried creating inventory before calling ' . InvLibHandler::class . 'register');
		}
	}
	
	final public function send(Player $player) : void{
		$pos = $player->getPosition();
		$this->holder = $holder = new Position((int) $pos->x, (int) $pos->y, (int) $pos->z, $pos->world);
		$type = $this->type;
		$network = $player->getNetworkSession();
		$x = $holder->x;
		$y = $holder->y;
		$z = $holder->z;
		$blockId = BlockFactory::getInstance()->get($type->getBlockId(), 0)->getFullId();
		$double = $type->isDouble();
		$nbt = CompoundTag::create()
			->setString('id', 'Chest')
			->setInt('Chest', 1)
			->setString('CustomName', $this->title)
			->setInt('x', $x)
			->setInt('y', $y)
			->setInt('z', $z);
		if($double){
			$x2 = $x + 1;
			$nbt->setInt('pairx', $x2)->setInt('pairz', $z);
			self::sendBlock($x2, $y, $z, $network, $blockId);
		}
		self::sendBlock($x, $y, $z, $network, $blockId);
		$pk = BlockActorDataPacket::create(new BlockPosition($x,$y,$z), new CacheableNbt($nbt));
		$network->addToSendBuffer($pk);
		if($double){
			InvLibHandler::getScheduler()->scheduleDelayedTask(new ClosureTask(function() use($player) :void{
				if(!$player->isConnected()) return;
				$player->setCurrentWindow($this);
			}), 8);
		}else{
			$player->setCurrentWindow($this);
		}
	}

	public function onClose(Player $who) : void{
		parent::onClose($who);
		$network = $who->getNetworkSession();
		$holder = $this->holder;
		$x = $holder->x;
		$y = $holder->y;
		$z = $holder->z;
		$world = $holder->world;
		$block = $world->getBlockAt($x, $y, $z);
		self::sendBlock($x, $y, $z, $network, $block->getFullId());
		$tile = $world->getTileAt($x, $y, $z);
		if($tile instanceof Spawnable){
			$pk = BlockActorDataPacket::create(new BlockPosition($x,$y,$z), $tile->getSerializedSpawnCompound());
			$network->addToSendBuffer($pk);
		}
		if($this->type->isDouble()){
			$x += 1;
			$block = $world->getBlockAt($x, $y, $z);
			self::sendBlock($x, $y, $z, $network, $block->getFullId());
			$tile = $world->getTileAt($x, $y, $z);
			if($tile instanceof Spawnable){
				$pk = BlockActorDataPacket::create(new BlockPosition($x,$y,$z), $tile->getSerializedSpawnCompound());
				$network->addToSendBuffer($pk);
			}
		}
	}

	// If it returns false, the event is canceled.
	public function onTransaction(InvLibAction $action) : bool{}

	final public function getTitle() : string{
		return $this->title;
	}

	final public function setTitle(string $title) : void{
		$this->title = $title;
	}

	final public function getTypeInfo() : LibInvType{
		return $this->type;
	}

}