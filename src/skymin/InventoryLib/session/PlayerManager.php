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

namespace skymin\InventoryLib\session;

use pocketmine\event\EventPriority;
use pocketmine\event\player\{PlayerJoinEvent, PlayerQuitEvent};
use pocketmine\inventory\Inventory;
use pocketmine\network\mcpe\protocol\ContainerOpenPacket;
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use pocketmine\player\Player;
use pocketmine\utils\SingletonTrait;
use skymin\event\EventHandler;
use skymin\InventoryLib\inventory\BaseInventory;

final class PlayerManager{
	use SingletonTrait;

	/** @var PlayerSession[] */
	private static array $sessions = [];

	public function __construct(){
		self::setInstance($this);
	}

	public function get(Player $player) : ?PlayerSession{
		return self::$sessions[$player->getId()] ?? null;
	}

	#[EventHandler(EventPriority::MONITOR)]
	public function onJoin(PlayerJoinEvent $ev) : void{
		$player = $ev->getPlayer();
		$network = $player->getNetworkSession();
		$callbacks = $network->getInvManager()?->getContainerOpenCallbacks();
		if($callbacks === null) return;
		$previous = $callbacks->toArray();
		$callbacks->clear();
		$callbacks->add(function(int $id, Inventory $inv) : ?array{
			if(!$inv instanceof BaseInventory) return null;
			return [ContainerOpenPacket::blockInv(
				$id,
				$inv->getTypeInfo()->getWindowType(),
				BlockPosition::fromVector3($inv->getHolder())
			)];
		}, ...$previous);
		self::$sessions[$player->getId()] = new PlayerSession($network);
	}

	#[EventHandler(EventPriority::MONITOR)]
	public function onQuit(PlayerQuitEvent $ev) : void{
		unset(self::$sessions[$ev->getPlayer()->getId()]);
	}

}
