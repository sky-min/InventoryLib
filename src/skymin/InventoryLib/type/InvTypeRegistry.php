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

namespace skymin\InventoryLib\type;

use InvalidArgumentException;
use pocketmine\block\VanillaBlocks;
use pocketmine\network\mcpe\protocol\types\inventory\WindowTypes;

final class InvTypeRegistry{

	/** @var array<string, InvType> */
	private array $types = [];

	public function __construct(){
		$this->register(
			InvTypeIds::CHEST,
			new InvType(27, WindowTypes::CONTAINER, VanillaBlocks::CHEST())
		);
		$this->register(
			InvTypeIds::DOUBLE_CHEST,
			new InvType(54, WindowTypes::CONTAINER, VanillaBlocks::CHEST(), true)
		);
		$this->register(
			InvTypeIds::HOPPER,
			new InvType(5, WindowTypes::HOPPER, VanillaBlocks::HOPPER())
		);
	}

	public function register(string $identifier, InvType $type) : void{
		if(isset($this->types[$identifier])){
			throw new InvalidArgumentException("$identifier is already used by another InvType");
		}
		$this->types[$identifier] = $type;
	}

	public function exists(string $identifier) : bool{
		return isset($this->types[$identifier]);
	}

	public function get(string $identifier) : InvType{
		if(isset($this->types[$identifier])){
			return $this->types[$identifier];
		}
		throw new InvalidArgumentException("$identifier is not registered");
	}

}