<?php
declare(strict_types = 1);

namespace skymin\InventoryLib;

use pocketmine\player\Player;
use pocketmine\item\Item;

final class InvLibAction{
	
	private bool $cancelBool = false;
	
	public function __construct(private Player $player, private int $slot, private Item $sourceItem, private Item $targetItem){}
	
	public function getPlayer() :Player{
		return $this->player;
	}
	
	public function getSlot() :int{
		return $this->slot;
	}
	
	public function getSourceItem() :Item{
		return $this->sourceItem;
	}
	
	public function getTargetItem() :Item{
		return $this->targetItem;
	}
	
	public function setCancelled(bool $bool = true) :void{
		$this->cancelBool = $bool;
	}
	
	public function isCancelled() :bool{
		return $this->cancelBool;
	}
	
}