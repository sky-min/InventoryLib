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

use skymin\InventoryLib\session\PlayerManager;
use skymin\InventoryLib\type\InvTypeRegistry;

use pocketmine\plugin\Plugin;
use pocketmine\scheduler\TaskScheduler;

use skymin\event\EventManager;

final class InvLibHandler{

	private function __construct(){
		//NOOP
	}

	private static ?InvTypeRegistry $registry = null;

	public static function register(Plugin $plugin) : void{
		if(self::$registry === null){
			EventManager::register(new PlayerManager(), $plugin);
			EventManager::register(new EventListener(), $plugin);
			self::$registry = new InvTypeRegistry();
		}
	}

	public static function isRegistered() : bool{
		return self::$registry !== null;
	}

	public static function getRegistry() : ?InvTypeRegistry{
		return self::$registry;
	}

}
