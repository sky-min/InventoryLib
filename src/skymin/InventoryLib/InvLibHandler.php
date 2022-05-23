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

use skymin\InventoryLib\action\InvAction;
use skymin\InventoryLib\inventory\BaseInventory;

use pocketmine\Server;
use pocketmine\plugin\Plugin;
use pocketmine\world\Position;
use pocketmine\scheduler\TaskScheduler;
use pocketmine\event\EventPriority;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\inventory\transaction\action\SlotChangeAction;

final class InvLibHandler{

	private static ?TaskScheduler $scheduler = null;

	public static function register(Plugin $plugin) : void{
		if(self::$scheduler === null){
			self::$scheduler = $plugin->getScheduler();
			Server::getInstance()->getPluginManager()->registerEvent(InventoryTransactionEvent::class, function(InventoryTransactionEvent $ev) : void{
				$transaction = $ev->getTransaction();
				foreach($transaction->getActions() as $action){
					if(!$action instanceof SlotChangeAction) continue;
					$inventory = $action->getInventory();
					if(!$inventory instanceof BaseInventory) continue;
					if($inventory->onTransaction(new InvLibAction(
						$transaction->getSource(),
						$action->getSlot(),
						$action->getSourceItem(),
						$action->getTargetItem()
					))){
						$ev->cancel();
					}
				}
			}, EventPriority::HIGHEST, $plugin);
		}
	}

	public static function getScheduler() : ?TaskScheduler{
		return self::$scheduler;
	}

}
