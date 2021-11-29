<?php
declare(strict_types = 1);

namespace skymin\InventoryLib;

use pocketmine\Server;
use pocketmine\plugin\Plugin;

final class InventoryLib{
	
	public static ?Plugin $register = null;
	
	public static function register(Plugin $plugin) :void{
		if(self::$register === null){
			self::$register = $plugin;
		}
	}
	
}