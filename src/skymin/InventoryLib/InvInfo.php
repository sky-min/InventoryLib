<?php
declare(strict_types = 1);

namespace skymin\InventoryLib;

use pocketmine\utils\EnumTrait;
use pocketmine\block\BlockLegacyIds;
use pocketmine\network\mcpe\protocol\types\inventory\WindowTypes;


/**
 * @method static self TYPE_CHEST()
 * @method static self TYPE_DOUBLE_CHEST()
 * @method static self TYPE_DROPPER()
 * @method static self TYPE_HOPPER()
 */
final class InvInfo{
	use EnumTrait{
		__construct as Enum_construct;
	}
	
	protected static function setup() :void{
		self::registerAll(
			new self('type_chest'),
			new self('type_double_chest'),
			new self('type_dropper'),
			new self('type_hopper')
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
			self::TYPE_CHEST(), self::TYPE_DOUBLE_CHEST() => WindowTypes::CONTAINER,
			self::TYPE_DROPPER() => WindowTypes::DISPENSER,
			self::TYPE_HOPPER() => WindowTypes::HOPPER,
			default => WindowTypes::CONTAINER
		}
	}
	
	public function getSize() :int{
		return match($this->id()){
			self::TYPE_CHEST() => 27,
			self::TYPE_DOUBLE_CHEST() => 54,
			self::TYPE_DROPPER() => 9,
			self::TYPE_HOPPER() => 5,
			default => 27
		}
	}
	
	public function getBlockId() :int{
		return match($this->id()){
			self::TYPE_CHEST(), self::TYPE_DOUBLE_CHEST() => BlockLegacyIds::CHEST,
			self::TYPE_DROPPER() => BlockLegacyIds::DROPPER,
			self::TYPE_HOPPER() => BlockLegacyIds::HOPPER_BLOCK,
			default => BlockLegacyIds::CHEST
		}
	}
	
}