<?php
declare(strict_types = 1);

namespace skymin\InventoryLib\session;

use pocketmine\Server;
use pocketmine\plugin\Plugin;
use pocketmine\player\Player;
use pocketmine\utils\SingletonTrait;
use pocketmine\event\EventPriority;
use pocketmine\event\player\{PlayerJoinEvent, PlayerQuitEvent};

final class PlayerManager{
	use SingletonTrait;

	/** @var PlayerSession[] */
	private static array $sessions = [];

	public function __construct(Plugin $plugin){
		self::setInstance($this);
		$pluginManager = Server::getInstance()->getPluginManager();
		$pluginManager->registerEvent(PlayerJoinEvent::class, function(PlayerJoinEvent $ev) : void{
			$player = $ev->getPlayer();
			static::$sessions[spl_object_id($player)] = new PlayerSession($player->getNetworkSession());
		}, EventPriority::MONITOR,  $plugin);
		$pluginManager->registerEvent(PlayerQuitEvent::class, function(PlayerQuitEvent $ev) : void{
			$id = spl_object_id($ev->getPlayer());
			static::$sessions[$id]->reset();
			unset(static::$sessions[$id]);
		}, EventPriority::MONITOR, $plugin);
	}

	public function get(Player $player) : ?PlayerSession{
		return self::$sessions[spl_object_id($player)] ?? null;
	}

}