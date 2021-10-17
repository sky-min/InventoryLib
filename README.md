# InventoryAPI
PocketMine-MP APIv4.0.0

## how to use
OneBlockInventory
```php

```
DoubleChestInventory
```php
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;

use skymin\InventoryAPI\DoubleChestInventory;

class TestInv extends DoubleChestInventory{
	
	public function __construct(Player $player, PluginBase $plugin){
		parent::__construct($plugin->getScheduler(), $player->getPosition(), 'test');
	}
	
}
```