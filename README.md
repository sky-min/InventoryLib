# InventoryAPI
PocketMine-MP APIv4.0.0

## Example
### OneBlockInventory
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
### DoubleChestInventory
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
## how to send gui
### [SendInventory](https://github.com/pmmp/PocketMine-MP/blob/8db5732b44578a59c785e6e3c1d36c87c90ddeb4/src/player/Player.php#L2333)
