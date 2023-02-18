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

namespace skymin\InventoryLib\action;

use pocketmine\item\Item;
use pocketmine\player\Player;

final class InventoryAction{

	public function __construct(
		private readonly Player $player,
		private readonly int $slot,
		private readonly Item $sourceItem,
		private readonly Item $targetItem
	){
	}

	public function getPlayer() : Player{
		return $this->player;
	}

	public function getSlot() : int{
		return $this->slot;
	}

	public function getSourceItem() : Item{
		return $this->sourceItem;
	}

	public function getTargetItem() : Item{
		return $this->targetItem;
	}

}
