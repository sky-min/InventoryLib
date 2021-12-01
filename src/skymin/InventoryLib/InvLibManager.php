<?php
declare(strict_types = 1);

namespace skymin\InventoryLib;

use pocketmine\Server;
use pocketmine\plugin\Plugin;
use pocketmine\scheduler\TaskScheduler;

use const null;

final class InvLibManager{
	
	private ?TaskScheduler $scheduler = null;
	
	public static function register(Plugin $plugin) :void{
		if($this->scheduler === null){
			$this->scheduler = $plugin->getScheduler();
			$plugin->getServer()->getPluginManager()->registerEvents(new InvLibEventListener(), $plugin);
		}
	}
	
	public static function getScheduler() :?TaskScheduler{
		return $scheduler;
	}
	
}