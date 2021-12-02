<?php
declare(strict_types = 1);

namespace skymin\InventoryLib;

use pocketmine\utils\EnumTrait;
use pocketmine\block\BlockLegacyIds;
use pocketmine\network\mcpe\protocol\types\inventory\WindowTypes;


/**
 * @method static self CHEST()
 * @method static self DOUBLE_CHEST()
 * @method static self DROPPER()
 * @method static self HOPPER()
 */
final class LibInvType{
	use EnumTrait;
	
	protected static function setup() :void{
		self::registerAll(
			new self('chest'),
			new self('double_chest'),
			new self('dropper'),
			new self('hopper')
		);
	}
	
	public function isDouble() :bool{
		return ($this->id() === self::DOUBLE_CHEST()->id());
	}
	
	public function getWindowType() :int{
		return match($this->id()){
			self::CHEST()->id(), self::DOUBLE_CHEST()->id() => WindowTypes::CONTAINER,
			self::DROPPER()->id() => WindowTypes::DROPPER,
			self::HOPPER()->id() => WindowTypes::HOPPER
		};
	}
	
	public function getSize() :int{
		return match($this->id()){
			self::CHEST()->id() => 27,
			self::DOUBLE_CHEST()->id() => 54,
			self::DROPPER()->id() => 9,
			self::HOPPER()->id() => 5
		};
	}
	
	public function getBlockId() :int{
		return match($this->id()){
			self::CHEST()->id(), self::DOUBLE_CHEST()->id() => 54,
			self::DROPPER()->id() => BlockLegacyIds::DROPPER,
			self::HOPPER()->id() => BlockLegacyIds::HOPPER_BLOCK
		};
	}
	
}