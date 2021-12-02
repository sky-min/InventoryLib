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
	use EnumTrait{
		__construct as Enum_construct;
	}
	
	protected static function setup() :void{
		self::registerAll(
			new self('chest'),
			new self('double_chest'),
			new self('dropper'),
			new self('hopper')
		);
	}
	
	private bool $double = false;
	
	private function __construct(string $type){
		$this->Enum_construct($type);
		if($type === 'type_double_chest'){
			$this->double = true;
		}
	}
	
	public function isDouble() :bool{
		return $this->double;
	}
	
	public function getWindowType() :int{
		return match($this->id()){
			self::CHEST(), self::DOUBLE_CHEST() => WindowTypes::CONTAINER,
			self::DROPPER() => WindowTypes::DISPENSER,
			self::HOPPER() => WindowTypes::HOPPER,
			default => WindowTypes::CONTAINER
		};
	}
	
	public function getSize() :int{
		return match($this->id()){
			self::CHEST() => 27,
			self::DOUBLE_CHEST() => 54,
			self::DROPPER() => 9,
			self::HOPPER() => 5,
			default => 27
		};
	}
	
	public function getBlockId() :int{
		return match($this->id()){
			self::CHEST(), self::DOUBLE_CHEST() => BlockLegacyIds::CHEST,
			self::DROPPER() => BlockLegacyIds::DROPPER,
			self::HOPPER() => BlockLegacyIds::HOPPER_BLOCK,
			default => BlockLegacyIds::CHEST
		};
	}
	
}