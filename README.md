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
	public function __construct(InvInfo $info){
		parent::__construct(InvInfo $info);
	}
}
```