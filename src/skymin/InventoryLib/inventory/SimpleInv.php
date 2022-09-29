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

use skymin\InventoryLib\action\InventoryAction;

use pocketmine\utils\Utils;
use pocketmine\player\Player;

use Closure;

final class SimpleInv extends BaseInventory{

	public static function create(string $identifier, string $title = '') : self{
		return new self($identifier, $title);
	}

	private ?Closure $actionHandler = null;
	private ?Closure $closeHandler = null;

	public function setActionHandler(?Closure $handler) : void{
		if($handler === null){
			$this->actionHandler = $handler;
			return;
		}
		Utils::validateCallableSignature(function(SimpleInv $inventory,  InventoryAction $action) : bool{}, $handler);
		$this->actionHandler = $handler;
	}

	public function setCloseHandler(?Closure $handler) : void{
		if($handler === null){
			$this->closeHandler = $handler;
			return;
		}
		Utils::validateCallableSignature(function(SimpleInv $inventory,  Player $player) : void{}, $handler);
		$this->closeHandler = $handler;
	}

	public function onClose(Player $who) : void{
		parent::onClose($who);
		$handler = $this->closeHandler;
		if($handler !== null){
			$handler($this, $who);
		}
	}

	public function onAction(InventoryAction $action) : bool{
		$handler = $this->actionHandler;
		if($handler !== null){
			return $handler($this, $action);
		}
		return true;
	}

}