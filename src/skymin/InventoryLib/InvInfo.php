<?php
declare(strict_types = 1);

namespace skymin\InventoryLib;

use pocketmine\world\Position;

final class InvInfo{
	
	public const TYPE_CHEST = 0;
	public const TYPE_DOUBLE_CHEST = 1;
	public const TYPE_DROPPER = 2;
	public const TYPE_HOPPER = 3;
	
	public Position $holder;
	public int $windowType = 0;
	public int $size;
	public int $blockId;
	
	private bool $double = false;
	
	public function __construct(int $type, Position $holder, public string $title){
		$this->holder = new Position((int) $holder->x, (int) $holder->y, (int) $holder->z, $holder->world);
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
	
	public function addPosition(int $x = 0, int $y = 0, int $z = 0) :self{
		$holder = $this->holder;
		$this->holder = new Position($holder->x + $x, $holder->y + $y, $holder->z + $z, $holder->world);
		return $this;
	}
	
}