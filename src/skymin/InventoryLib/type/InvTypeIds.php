<?php

declare(strict_types = 1);

namespace skymin\InventoryLib\type;

final class InvTypeIds{

	private function __construct(){
		//NOOP
	}

	public const CHEST = 'invlib:chest';
	public const DOUBLE_CHEST = 'invlib:double_chest';
	public const HOPPER = 'invlib:hopper';

}