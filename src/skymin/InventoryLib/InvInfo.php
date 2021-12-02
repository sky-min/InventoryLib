<?php
declare(strict_types = 1);

namespace skymin\InventoryLib;

use pocketmine\utils\EnumTrait;

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
		
	}
	
	public function getSize() :int{
		
	}
	
	public function getBlockId() :int{
		
	}
	
}