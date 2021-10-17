# InventoryAPI
PocketMine-MP APIv4.0.0

## how to use
OneBlockInventory
```php
use pocketmine\block\BlockLegacyIds;
use pocketmine\player\Player;

use pocketmine\network\mcpe\protocol\types\inventory\WindowTypes;

use skymin\InventoryAPI\OneBlockInventory;

class TestInv extends OneBlockInventory{
	
	public function __construct(Player $player){
		parent::__construct($player->getPosition(), BlockLegacyIds::HOPPER_BLOCK, WindowTypes::HOPPER, 5, 'test')
	}
	
}
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