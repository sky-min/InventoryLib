# InventoryLib
PocketMine-MP APIv4.0.0

# How to use
register `InvLibManager` during plugin enable
```php
InvLibManager::register($this);
```
## create custom inventory class
```php
class TestInv extends LibInventory{
	//code
}
```
## how to send gui
### [SendInventory](https://github.com/pmmp/PocketMine-MP/blob/8db5732b44578a59c785e6e3c1d36c87c90ddeb4/src/player/Player.php#L2333)
