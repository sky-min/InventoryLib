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

use pocketmine\Server;
use pocketmine\plugin\Plugin;
use pocketmine\scheduler\TaskScheduler;
use pocketmine\world\Position;

use const null;

final class InvLibManager{
	
	private static ?TaskScheduler $scheduler = null;
	
	public static function register(Plugin $plugin) :void{
		if(self::$scheduler === null){
			self::$scheduler = $plugin->getScheduler();
			$plugin->getServer()->getPluginManager()->registerEvents(new InvLibEventListener(), $plugin);
		}
	}
	
	public static function getScheduler() :?TaskScheduler{
		return self::$scheduler;
	}
	
	public static function create(LibInvType $info, Position $holder, string $title = '') :LibInventory{
		return new LibInventory($info, $holder, $title);
	}
	
}
