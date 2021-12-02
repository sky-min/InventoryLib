<?php
declare(strict_types = 1);

namespace skymin\InventoryLib;

use pocketmine\world\Position;

final class InvInfo{
	
	public const TYPE_CHEST = 0;
	public const TYPE_DOUBLE_CHEST = 1;
	public const TYPE_DROPPER = 2;
	public const TYPE_HOPPER = 3;
	
	private int $windowType;
	private int $size;
	private int $blockId;
	
	private bool $double = false;
	
	public function __construct(int $type, private string $title){
		[$this->windowType, $this->size, $this->blockId] = match($type){
			self::TYPE_CHEST => [0, 27, 54],
			self::TYPE_DOUBLE_CHEST => [0, 54, 54],
			self::TYPE_DROPPER => [6, 9, 125],
			self::TYPE_HOPPER => [8, 5, 154],
			default => [0, 27, 54]
		};
		if($type === self::TYPE_DOUBLE_CHEST){
			$this->double = true;
		}
	}
	
	public function isDouble() :bool{
		return $this->double;
	}
	
	public function getTitle() :string{
		return $this->title;
	}
	
	public function getWindowType() :int{
		return $this->windowType;
	}
	
	public function getSize() :int{
		return $this->size;
	}
	
	public function getBlockId() :int{
		return $this->blockId;
	}
	
}