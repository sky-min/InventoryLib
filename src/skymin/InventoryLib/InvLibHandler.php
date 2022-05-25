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

use skymin\InventoryLib\action\InventoryAction;
use skymin\InventoryLib\inventory\BaseInventory;
use skymin\InventoryLib\session\PlayerManager;

use pocketmine\Server;
use pocketmine\plugin\Plugin;
use pocketmine\world\Position;
use pocketmine\scheduler\TaskScheduler;
use pocketmine\event\EventPriority;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\event\inventory\{InventoryOpenEvent, InventoryTransactionEvent};
use pocketmine\network\mcpe\protocol\ContainerOpenPacket;
use pocketmine\inventory\transaction\action\{SlotChangeAction, DropItemAction};

use function reset;

final class InvLibHandler{

	private static ?TaskScheduler $scheduler = null;

	public static function register(Plugin $plugin) : void{
		if(self::$scheduler === null){
			self::$scheduler = $plugin->getScheduler();
			new PlayerManager($plugin);
			$pluginManager = Server::getInstance()->getPluginManager();
			$pluginManager->registerEvent(InventoryTransactionEvent::class, static function(InventoryTransactionEvent $ev) : void{
				$transaction = $ev->getTransaction();
				foreach($transaction->getActions() as $action){
					$inventory = $action->getInventory();
					if(!$inventory instanceof BaseInventory) continue;
					if(!$action instanceof SlotChangeAction) continue;
					if(!$inventory->onAction(new InventoryAction(
						$transaction->getSource(),
						$action->getSlot(),
						$action->getSourceItem(),
						$action->getTargetItem()
					))){
						$ev->cancel();
					}elseif($ev->isCancelled()){
						$ev->uncancel();
					}
				}
			}, EventPriority::HIGHEST, $plugin);
			$pluginManager->registerEvent(InventoryOpenEvent::class, static function(InventoryOpenEvent $ev) : void{
				if(!$ev->isCancelled()) return;
				$inventory = $ev->getInventory();
				if($inventory instanceof BaseInventory){
					$inventory->sendRealBlock($player);
				}
			}, EventPriority::MONITOR, $plugin, true);
			$pluginManager->registerEvent(DataPacketSendEvent::class, static function(DataPacketSendEvent $ev) : void{
				$packets = $ev->getPackets();
				if(count($packets) !== 1) return;
				$packet = reset($packets);
				if(!$packet instanceof ContainerOpenPacket) return;
				$targets = $ev->getTargets();
				if(count($targets) !== 1) return;
				$target = reset($targets);
				$invManager = $target->getInvManager();
				if($invManager === null) return;
				$inv = $invManager->getWindow($packet->windowId);
				if($inv instanceof BaseInventory){
					$packet->windowType = $inv->getTypeInfo()->getWindowType();
				}
			}, EventPriority::MONITOR, $plugin);
		}
	}

	public static function getScheduler() : ?TaskScheduler{
		return self::$scheduler;
	}

}
