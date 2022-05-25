<?php
declare(strict_types = 1);

namespace skymin\InventoryLib\session;

use skymin\InventoryLib\InvLibHandler;
use skymin\InventoryLib\inventory\BaseInventory;

use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\scheduler\ClosureTask;

use pocketmine\event\inventory\InventoryCloseEvent;

use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\convert\RuntimeBlockMapping;

use pocketmine\network\mcpe\protocol\{
	BlockActorDataPacket,
	UpdateBlockPacket
};
use pocketmine\network\mcpe\protocol\types\{CacheableNbt, BlockPosition};

use function spl_object_id;

final class PlayerSession{

	private ?BaseInventory $current = null;

	public function __construct(private NetworkSession $network){}

	public function waitOpenWindow(BaseInventory $inv) : void{
		if($this->current !== null){
			$this->current->sendRealBlock($this->network->getPlayer());
		}
		$this->current = $inv;
		InvLibHandler::getScheduler()->scheduleDelayedTask(new ClosureTask(function() use($inv): void{
			if($inv !== $this->current) return;
			if($this->network->isConnected()){
				$this->network->getPlayer()->setCurrentWindow($inv);
			}
		}), 8);
	}

	public function closeWindow() : void{
		$current = $this->current;
		if($current !== null){
			$player = $this->network->getPlayer();
			if($current === $player->getCurrentWindow()){
				(new InventoryCloseEvent($current, $player))->call();
				$current->onClose($player);
				(fn() => $this->currentWindow = null)->call($player);
			}
			$current->sendRealBlock($player);
			$this->current = null;
		}
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