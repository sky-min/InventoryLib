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

declare(strict_types=1);

namespace skymin\InventoryLib;

use pocketmine\event\EventPriority;
use pocketmine\event\inventory\{InventoryOpenEvent, InventoryTransactionEvent};
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\inventory\Inventory;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\network\mcpe\protocol\ContainerOpenPacket;
use pocketmine\network\mcpe\protocol\NetworkStackLatencyPacket;
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use skymin\event\EventHandler;
use skymin\InventoryLib\action\InventoryAction;
use skymin\InventoryLib\inventory\BaseInventory;
use skymin\InventoryLib\session\PlayerManager;

final class EventListener implements Listener{

	private PlayerManager $manager;

	public function __construct(){
		$this->manager = PlayerManager::getInstance();
	}

	#[EventHandler(EventPriority::MONITOR)]
	public function onJoin(PlayerJoinEvent $ev) : void{
		$player = $ev->getPlayer();
		$network = $player->getNetworkSession();
		$callbacks = $network->getInvManager()?->getContainerOpenCallbacks();
		if($callbacks === null) return;
		$callbacks->clear();
		$callbacks->add(function(int $id, Inventory $inv) use ($player) : ?array{
			if(!$inv instanceof BaseInventory) return null;
			return [ContainerOpenPacket::blockInv(
				$id,
				$inv->getTypeInfo()->getWindowType(),
				BlockPosition::fromVector3($inv->getHolder($player))
			)];
		});
		$this->manager->createSession($player);
	}

	#[EventHandler(EventPriority::MONITOR)]
	public function onQuit(PlayerQuitEvent $ev) : void{
		$this->manager->closeSession($ev->getPlayer());
	}

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

	#[EventHandler(EventPriority::MONITOR, true)]
	public function onInvOpen(InventoryOpenEvent $ev) : void{
		if(!$ev->isCancelled()) return;
		$inventory = $ev->getInventory();
		if($inventory instanceof BaseInventory){
			$this->manager->get($ev->getPlayer())->sendRealBlock($inventory);
		}
	}

	#[EventHandler(EventPriority::MONITOR)]
	public function onDataRecieve(DataPacketReceiveEvent $ev) : void{
		if($ev->getPacket() instanceof NetworkStackLatencyPacket){
			$player = $ev->getOrigin()->getPlayer();
			if($player !== null){
				PlayerManager::getInstance()->get($player)?->notify();
			}
		}
	}

}