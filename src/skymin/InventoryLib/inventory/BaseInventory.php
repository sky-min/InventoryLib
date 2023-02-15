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
use skymin\InventoryLib\type\{
	InvType,
	InvTypeRegistry
};

use pocketmine\player\Player;
use pocketmine\inventory\SimpleInventory;
use pocketmine\block\inventory\{BlockInventory, BlockInventoryTrait};

use pocketmine\world\Position;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\block\BlockFactory;
use pocketmine\block\tile\Spawnable;
use pocketmine\network\mcpe\protocol\types\CacheableNbt;

use LogicException;

abstract class BaseInventory extends SimpleInventory implements BlockInventory{
	use BlockInventoryTrait;

	private PlayerManager $player_manager;

	private InvType $type;

	public function __construct(string $identifier, private string $title = ''){
		if(!InvLibHandler::isRegistered()){
			throw new LogicException('Tried creating inventory before calling ' . InvLibHandler::class . 'register');
		}
		$this->type = InvLibHandler::getRegistry()->get($identifier);
		parent::__construct($this->type->getSize());
		$this->player_manager = PlayerManager::getInstance();
	}

	final public function send(Player $player) : void{
		$pos = $player->getPosition();
		$vec = $player->getDirectionVector()->multiply(-3)->addVector($pos);
		if($vec->y + 1 < -64){
			$vec->y += 1;
		}elseif($vec->y - 1 > 256){
			$vec->y -= 1;
		}
		$this->holder = $holder = new Position((int) $vec->x, (int) $vec->y, (int) $vec->z, $pos->world);
		$session = $this->player_manager->get($player);
		$session->waitOpenWindow($this);
		$type = $this->type;
		$blockId = $type->getBlockId();
		$nbt = CompoundTag::create()
			->setString('CustomName', $this->title)
			->setInt('x', $holder->x)
			->setInt('y', $holder->y)
			->setInt('z', $holder->z);
		if($type->isDouble()){
			$nbt->setInt('pairx', $holder->x + 1)->setInt('pairz', $holder->z);
			$session->sendBlock($holder->add(1, 0, 0), $blockId);
		}
		$session->sendBlock($holder, $blockId, new CacheableNbt($nbt));
	}

	public function onClose(Player $who) : void{
		parent::onClose($who);
		$this->player_manager->get($who)->onClose($this);
	}

	// If it returns false, the event is canceled.
	abstract public function onAction(InventoryAction $action) : bool;

	final public function close(Player $player) : void{ 
		$this->player_manager->get($player)->closeWindow();
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
