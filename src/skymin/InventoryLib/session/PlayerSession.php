<?php
declare(strict_types = 1);

namespace skymin\InventoryLib\session;

use skymin\InventoryLib\InvLibHandler;
use skymin\InventoryLib\inventory\BaseInventory;

use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\scheduler\ClosureTask;

use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\convert\RuntimeBlockMapping;

use pocketmine\network\mcpe\protocol\{
	BlockActorDataPacket,
	UpdateBlockPacket
};
use pocketmine\network\mcpe\protocol\types\{CacheableNbt, BlockPosition};

use function spl_object_id;

final class PlayerSession{

	private array $stack = [];

	public function __construct(private NetworkSession $network){}

	public function reset() : void{
		foreach($this->stack as $key => $task){
			$task->getHandler()->cancel();
			unset($this->stack[$key]);
		}
	}

	public function waitOpen(BaseInventory $inv) : void{
		$task = new ClosureTask(function() use($inv): void{
			if($this->network->isConnected()){
				$this->network->getPlayer()->setCurrentWindow($inv);
			}
			unset($this->stack[spl_object_id($inv)]);
		});
		$this->stack[spl_object_id($inv)] = $task;
		InvLibHandler::getScheduler()->scheduleDelayedTask($task, 8 * count($this->stack));
	}

	public function sendBlock(Vector3 $pos, int $blockId, null|CompoundTag|CacheableNbt $tile = null) : void{
		$pos = BlockPosition::fromVector3($pos);
		$pk = UpdateBlockPacket::create(
			$pos,
			RuntimeBlockMapping::getInstance()->toRuntimeId($blockId),
			UpdateBlockPacket::FLAG_NETWORK,
			UpdateBlockPacket::DATA_LAYER_NORMAL
		);
		$this->network->sendDataPacket($pk);
		if($tile !== null){
			$pk = BlockActorDataPacket::create($pos, $tile instanceof CacheableNbt ? $tile : new CacheableNbt($tile));
			$this->network->sendDataPacket($pk);
		}
	}

}