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

use pocketmine\event\EventPriority;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\event\inventory\{InventoryOpenEvent, InventoryTransactionEvent};
use pocketmine\inventory\transaction\action\SlotChangeAction;

use skymin\event\EventHandler;

final class EventListener{

	#[EventHandler]
	public function onInvTransaction(InventoryTransactionEvent $ev) : void{
		$transaction = $ev->getTransaction();
		foreach($transaction->getActions() as $action){
			if(!$action instanceof SlotChangeAction) continue;
			$inventory = $action->getInventory();
			if(!$inventory instanceof BaseInventory) continue;
			if(!$inventory->onAction(new InventoryAction(
				$transaction->getSource(),
				$action->getSlot(),
				$action->getSourceItem(),
				$action->getTargetItem()
			))){
				$ev->cancel();
			}
		}
	}

	#[EventHandler(
		EventPriority::MONITOR,
		true
	)]
	public function onInvOpen(InventoryOpenEvent $ev) : void{
		if(!$ev->isCancelled()) return;
		$inventory = $ev->getInventory();
		if($inventory instanceof BaseInventory){
			$inventory->sendRealBlock($ev->getPlayer());
		}
	}

}