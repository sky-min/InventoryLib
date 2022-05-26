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
use skymin\InventoryLib\session\PlayerManager;

use pocketmine\player\Player;
use pocketmine\inventory\SimpleInventory;
use pocketmine\block\inventory\{BlockInventory, BlockInventoryTrait};

use pocketmine\world\Position;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\block\tile\Spawnable;
use pocketmine\block\{Block, BlockFactory};

use function spl_object_id;

abstract class BaseInventory extends SimpleInventory implements BlockInventory{
	use BlockInventoryTrait;

	public function __construct(private InvType $type, private string $title = ''){
		parent::__construct($this->type->getSize());
		if(InvLibHandler::getScheduler() === null){
			throw new \LogicException('Tried creating inventory before calling ' . InvLibHandler::class . 'register');
		}
	}

	final public function send(Player $player) : void{
		$pos = $player->getPosition();
		$vec = $player->getDirectionVector()->multiply(-4)->addVector($pos);
		if($vec->y < -64 || $vec->y + 1 < -64){
			$vec->y += 1;
		}elseif($vec->y > 256 || $vec->y - 1 > 256){
			$vec->y -= 1;
		}
		$this->holder = $holder = new Position((int) $vec->x, (int) $vec->y, (int) $vec->z, $pos->world);
		$session = PlayerManager::getInstance()->get($player);
		$session->waitOpenWindow($this);
		$type = $this->type;
		$blockId = BlockFactory::getInstance()->get($type->getBlockId(), 0)->getFullId();
		$nbt = CompoundTag::create()
			->setString('id', 'Chest')
			->setInt('Chest', 1)
			->setString('CustomName', $this->title)
			->setInt('x', $holder->x)
			->setInt('y', $holder->y)
			->setInt('z', $holder->z);
		if($type->isDouble()){
			$nbt->setInt('pairx', $holder->x + 1)->setInt('pairz', $holder->z);
			$session->sendBlock($holder->add(1, 0, 0), $blockId);
		}
		$session->sendBlock($holder, $blockId, $nbt);
	}

	public function onClose(Player $who) : void{
		parent::onClose($who);
		$this->sendRealBlock($who);
	}

	// If it returns false, the event is canceled.
	abstract public function onAction(InventoryAction $action) : bool;

	final public function close(Player $player) : void{ 
		PlayerManager::getInstance()->get($player)->closeWindow();
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

	/** @internal */
	final public function sendRealBlock(Player $player) : void{
		$session = PlayerManager::getInstance()->get($player);
		$holder = $this->holder;
		$world = $holder->world;
		$vec = $holder->asVector3();
		$blockId = $world->getBlock($vec)->getFullId();
		$nbt = null;
		$tile = $world->getTile($vec);
		if($tile instanceof Spawnable){
			$nbt = $tile->getSerializedSpawnCompound();
		}
		$session->sendBlock($vec, $blockId, $nbt);
		if($this->type->isDouble()){
			$vec = $holder->add(1, 0, 0);
			$blockId = $world->getBlock($vec)->getFullId();
			$nbt = null;
			$tile = $world->getTile($vec);
			if($tile instanceof Spawnable){
				$nbt = $tile->getSerializedSpawnCompound();
			}
			$session->sendBlock($vec, $blockId, $nbt);
		}
	}

}