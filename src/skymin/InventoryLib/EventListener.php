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

use pocketmine\event\{Listener, EventPriority};
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\event\inventory\{InventoryOpenEvent, InventoryTransactionEvent};
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\network\mcpe\protocol\ContainerOpenPacket;

use skymin\event\{Priority, HandleCancelled};

final class EventListener implements Listener{

	#[Priority(EventPriority::HIGHEST)]
	public function omInvTransaction(InventoryTransactionEvent $ev) : void{
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

	#[handleCancelled, Priority(EventPriority::MONITOR)]
	public function onInvOpen(InventoryOpenEvent $ev) : void{
		if(!$ev->isCancelled()) return;
		$inventory = $ev->getInventory();
		if($inventory instanceof BaseInventory){
			$inventory->sendRealBlock($ev->getPlayer());
		}
	}

	#[Priority(EventPriority::MONITOR)]
	public function onContainerOpen(DataPacketSendEvent $ev) : void{
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
	}

}