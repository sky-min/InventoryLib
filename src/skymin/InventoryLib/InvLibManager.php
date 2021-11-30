<?php
declare(strict_types = 1);

namespace skymin\InventoryLib;

use pocketmine\Server;
use pocketmine\plugin\Plugin;

use const null;

final class InvLibManager{
	
	public static ?Plugin $register = null;
	
	public static function register(Plugin $plugin) :void{
		if(self::$register === null){
			self::$register = $plugin;
			$plugin->getServer()->getPluginManager()->registerEvents(new InvLibEventListener(), $plugin);
		}
	}
	
}