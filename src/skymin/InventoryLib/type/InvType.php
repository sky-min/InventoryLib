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

use pocketmine\block\Block;

final class InvType{

	private int $blockId;

	public function __construct(
		private readonly int $size,
		private readonly int $type,
		Block $block,
		private readonly bool $isDouble = false
	){
		$this->blockId = $block->getStateId();
	}

	public function isDouble() : bool{
		return $this->isDouble;
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
