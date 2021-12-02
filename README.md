# InventoryLib
[poggit](https://poggit.pmmp.io/ci/sky-min/InventoryLib)

# How to use
This description does not describe all features. You can contribute for pullrequest

---

register `InvLibManager` during plugin enable
```php
InvLibManager::register($this);
```
`$pos`'s type is pocketmine\world\Position


## Create custom inventory class
```php
class TestInv extends LibInventory{
	public function __construct(Position $pos){
		parent::__construct(LibInvType::DOUBLE_CHEST(),$pos, 'example');
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
$inv = new TestInv($pos);
$inv->send($player, function () use ($inv){
	$inv->setItem(3, ItemFactory::getInstance()->get(1));
});
```
