<?php
declare(strict_types = 1);

namespace skymin\InventoryLib;

use pocketmine\world\Position;

use function match;

final class InvInfo{
	
	public const TYPE_CHEST = 0;
	public const TYPE_DOUBLE_CHEST = 1;
	public const TYPE_DROPPER = 2;
	public const TYPE_HOPPER = 3;
	
	public int $windowType = 0;
	public int $size;
	public int $blockId;
	
	private bool $double = false;
	
	public function __construct(int $type, public Position $holder, public string $title){
		$this->windowType = match($type){
			self::TYPE_CHEST => 0,
			self::TYPE_DOUBLE_CHEST => 0,
			self::TYPE_DROPPER => 6,
			self::TYPE_HOPPER => 8,
			default => 0
		};
		$this->size = match($type){
			self::TYPE_CHEST => 27,
			self::TYPE_DOUBLE_CHEST => 54,
			self::TYPE_DROPPER => 9,
			self::TYPE_HOPPER => 5,
			default => 27
		};
		$this->blockId = match($type){
			self::TYPE_CHEST => 54,
			self::TYPE_DOUBLE_CHEST => 54,
			self::TYPE_DROPPER => 125,
			self::TYPE_HOPPER => 154,
			default => 54
		};
		if($type === self::TYPE_DOUBLE_CHEST){
			$this->double = true;
		}
	}
	
	public function isDouble() :bool{
		return $this->double;
	}
	
}