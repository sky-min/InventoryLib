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

use pocketmine\player\Player;
use pocketmine\utils\SingletonTrait;

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

	public function createSession(Player $player) : void{
		self::$sessions[$player->getId()] = new PlayerSession($player->getNetworkSession());
	}

	public function closeSession(Player $player) : void{
		unset(self::$sessions[$player->getId()]);
	}
}
