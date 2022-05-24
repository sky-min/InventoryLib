<?php
declare(strict_types = 1);

namespace skymin\InventoryLib\inventory;

use skymin\InventoryLib\action\InventoryAction;

use pocketmine\utils\Utils;
use pocketmine\player\Player;

use Closure;

final class SimpleInv extends BaseInventory{

	public static function create(private LibInvType $type, private string $title = '') : self{
		return new self($type, $title);
	}

	private ?Closure $actionHandler = null;
	private ?Closure $closeHandler = null;

	public function setActionHandler(?Closure $handler) : void{
		if($handler === null){
			$this->actionHandler = $handler;
			return;
		}
		Utils::validateCallableSignature(function(SimpleInv $inventory,  InventoryAction $action) : bool{}, $handler);
		$this->actionHandler = $handler;
	}

	public function setCloseHandler(?Closure $handler) : void{
		if($handler === null){
			$this->closeHandler = $handler;
			return;
		}
		Utils::validateCallableSignature(function(SimpleInv $inventory,  Player $player) : void{}, $handler);
		$this->closeHandler = $handler;
	}

	public function onClose(Player $who) : void{
		parent::onClose($who);
		$handler = $this->closeHandler;
		if($handler !== null){
			$handler($this, $who);
		}
	}

	public function onAction(InventoryAction $action) : bool{
		$handler = $this->actionHandler;
		if($handler !== null){
			return $handler($this, $action);
		}
		return true;
	}

}