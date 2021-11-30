# InventoryLib
PocketMine-MP APIv4.0.0

# How to use
register `InvLibManager` during plugin enable
```php
InvLibManager::register($this);
```
## InvInfo
`$position`'s type is pocketmine\world\Position
```php
$info = new InvInfo(InvInfo::CHEST, $position, 'test')
```
## Create custom inventory class
```php
class TestInv extends LibInventory{
	public function __construct(InvInfo $info){
		parent::__construct(InvInfo $info);
	}
	//code
	
	protected function onTransaction(InvLibAction $action) :void{
		if($action->getInput()->getId() === 1){
			$action->setCancel();
		}
	}
	
}
```
## SendInventory
```php
$inv = new TestInv($info);
$inv->send($player, function () use ($inv){
	$inv->setItem(3, ItemFactory::getInstance()->get(1));
});
```