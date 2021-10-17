# InventoryAPI
PocketMine-MP APIv4.0.0

## how to use
OneBlockInventory
```php

```
DoubleChestInventory
```php
use skymin\InventoryAPI\DoubleChestInventory;

class TestInv extends DoubleChestInventory{
	
	public function __construct(Position $pos, PluginBase $plugin){
		parent::__construct($plugin->getScheduler(), $pos, 'test');
	}
	
}
```