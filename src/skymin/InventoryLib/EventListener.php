<?php
declare(strict_types = 1);

namespace skymin\InventoryLib;

use skymin\InventoryLib\action\InventoryAction;
use skymin\InventoryLib\inventory\BaseInventory;

use pocketmine\event\{Listener, EventPriority};
use pocketmine\event\server\{DataPacketReceiveEvent, DataPacketSendEvent};
use pocketmine\event\inventory\{InventoryOpenEvent, InventoryTransactionEvent};
use pocketmine\inventory\transaction\action\{SlotChangeAction, DropItemAction};
use pocketmine\network\mcpe\protocol\ContainerOpenPacket;

final class EventListener implements Listener{

	/** @priority HIGHEST */
	public function omInvTransaction(InventoryTransactionEvent $ev) : void{
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
			}
		}
	}

	/**
	 * @handleCancelled
	 * @priority MONITOR
	 */
	public function onInvOpen(InventoryOpenEvent $ev) : void{
		if(!$ev->isCancelled()) return;
		$inventory = $ev->getInventory();
		if($inventory instanceof BaseInventory){
			$inventory->sendRealBlock($player);
		}
	}

	/** @priority MONITOR */
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