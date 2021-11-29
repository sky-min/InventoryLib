<?php
declare(strict_types = 1);

namespace skymin\InventoryLib\util;

use pocketmine\item\Item;
use pocketmine\player\Player;

trait EventTrait{
	
	protected function onTransaction(Player $player,int $slot, Item $sourceItem, Item $targetItem) :bool{
		return true;
	}
	
}