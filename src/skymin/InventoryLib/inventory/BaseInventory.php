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

namespace skymin\InventoryLib\inventory;

use LogicException;
use pocketmine\inventory\SimpleInventory;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\CacheableNbt;
use pocketmine\player\Player;
use pocketmine\world\Position;
use pocketmine\world\World;
use skymin\InventoryLib\action\InventoryAction;
use skymin\InventoryLib\InvLibHandler;
use skymin\InventoryLib\session\PlayerManager;
use skymin\InventoryLib\type\{InvType};

abstract class BaseInventory extends SimpleInventory{

	private InvType $type;

	/**
	 * @var Position[]
	 * @phpstan-var array<int, Position>
	 */
	private array $holders = [];

	public function __construct(string $identifier, private string $title = ''){
		if(!InvLibHandler::isRegistered()){
			throw new LogicException('Tried creating inventory before calling ' . InvLibHandler::class . 'register');
		}
		$this->type = InvLibHandler::getRegistry()->get($identifier);
		parent::__construct($this->type->getSize());
	}

	final public function send(Player $player) : void{
		$pos = $player->getPosition();
		$vec = $player->getDirectionVector()->multiply(-3)->addVector($pos);
		if($vec->y + 1 < World::Y_MIN){
			$vec->y += 1;
		}elseif($vec->y - 1 > World::Y_MAX){
			$vec->y -= 1;
		}
		$holder = new Position((int) $vec->x, (int) $vec->y, (int) $vec->z, $pos->world);
		$this->holders[$player->getId()] = $holder;
		$session = PlayerManager::get($player);
		$session->waitOpenWindow($this);
		$type = $this->type;
		$blockId = $type->getBlockId();
		$nbt = CompoundTag::create()
			->setString('CustomName', $this->title)
			->setInt('x', $holder->x)
			->setInt('y', $holder->y)
			->setInt('z', $holder->z);
		if($type->isDouble()){
			$nbt->setInt('pairx', $holder->x + 1)->setInt('pairz', $holder->z);
			$session->sendBlock($holder->add(1, 0, 0), $blockId);
		}
		$session->sendBlock($holder, $blockId, new CacheableNbt($nbt));
	}

	public function onClose(Player $who) : void{
		parent::onClose($who);
		PlayerManager::get($who)->onClose($this);
	}

	// If it returns false, the event is canceled.
	abstract public function onAction(InventoryAction $action) : bool;

	final public function close(Player $player) : void{
		PlayerManager::get($player)->closeWindow();
	}

	final public function getTitle() : string{
		return $this->title;
	}

	final public function setTitle(string $title) : void{
		$this->title = $title;
	}

	final public function getTypeInfo() : InvType{
		return $this->type;
	}

	final public function getHolder(Player $player) : ?Position{
		return $this->holders[$player->getId()] ?? null;
	}
}
