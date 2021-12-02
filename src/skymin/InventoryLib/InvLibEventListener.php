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
*    /\____/\
* 　(∩`・ω・)
* ＿/_ミつ/￣￣￣/
* 　　＼/＿＿＿/
*
*/

declare(strict_types = 1);

namespace skymin\InventoryLib;

use pocketmine\event\Listener;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\inventory\transaction\action\SlotChangeAction;

final class InvLibEventListener implements Listener{
	
	public function onSlotChange(InventoryTransactionEvent $ev) :void{
		$transaction = $ev->getTransaction();
		foreach($transaction->getActions() as $action){
			if(!$action instanceof SlotChangeAction) continue;
			$inventory = $action->getInventory();
			if(!$inventory instanceof LibInventory) continue;
			(function() use($transaction, $action, $ev){
				if($this->onActionSenssor(new InvLibAction($transaction->getSource(), $action->getSlot(), $action->getSourceItem(), $action->getTargetItem()))){
					$ev->cancel();
				}
			})->call($inventory);
		}
	}
	
}