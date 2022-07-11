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

declare(strict_types = 1);

namespace skymin\InventoryLib\inventory;

use pocketmine\block\{
	Block,
	BlockFactory,
	BlockTypeIds,
	VanillaBlocks
};
use pocketmine\network\mcpe\protocol\types\inventory\WindowTypes;
use pocketmine\utils\EnumTrait;


/**
 * @method static self CHEST()
 * @method static self DOUBLE_CHEST()
 * @method static self DROPPER()
 * @method static self HOPPER()
 */
final class InvType{
	use EnumTrait{
		__construct as Enum_construct;
	}

	protected static function setup() : void{
		self::registerAll(
			new self('chest', 27, WindowTypes::CONTAINER, VanillaBlocks::CHEST()->getStateId()),
			new self('double_chest', 54, WindowTypes::CONTAINER, VanillaBlocks::CHEST()->getStateId()),
			//TODO: not yet added to pm5 new self('dropper', 9, WindowTypes::DROPPER, VanillaBlocks::DROPPER()->getStateId()), 
			new self('hopper', 5, WindowTypes::HOPPER, VanillaBlocks::HOPPER()->getStateId())
		);
	}

	private function __construct(
		string $name,
		private int $size,
		private int $type,
		private int $blockId
	){
		$this->Enum_construct($name);
	}

	public function isDouble() : bool{
		return $this->equals(self::DOUBLE_CHEST());
	}

	public function getSize() : int{
		return $this->size;
	}

	public function getWindowType() : int{
		return $this->type;
	}

	public function getBlockId() : int{
		return $this->blockId;
	}

}
