<?php
declare(strict_types = 1);

namespace skymin\InventoryLib\inventory;

use pocketmine\item\Item;
use pocketmine\player\Player;
use pocketmine\inventory\SimpleInventory;

use pocketmine\world\Position;

abstract class LibInventory extends SimpleInventory{
	
	public function __construct(protected Position $holder, protected string $title, int $size){
		parent::__construct($size);
	}
	
	abstract protected function onTransaction(Player $player,int $slot, Item $sourceItem, Item $targetItem) :bool;
	
	public function getName() :string{
		return $this->title;
	}
	
	public function getHolder() :Position{
		return $this->holder;
	}
	
	public function send(Player $player) :void{
		$player->setCurrentWindow($this);
	}
	
}