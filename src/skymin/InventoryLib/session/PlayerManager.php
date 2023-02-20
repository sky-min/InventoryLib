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
use function mt_rand;

final class PlayerManager{

	/** @var PlayerSession[] */
	private static array $sessions = [];

	private static int $waitId;

	/** @internal  */
	public static function init() : void{
		self::$waitId = mt_rand() * 1000;
	}

	public static function WaitId() : int{
		return self::$waitId;
	}

	public static function get(Player $player) : ?PlayerSession{
		return self::$sessions[$player->getId()] ?? null;
	}

	public static function createSession(Player $player) : void{
		self::$sessions[$player->getId()] = new PlayerSession($player->getNetworkSession());
	}

	public static function closeSession(Player $player) : void{
		unset(self::$sessions[$player->getId()]);
	}
}
