<?php
/**
 *      _                    _
 *  ___| | ___   _ _ __ ___ (_)_ __
 * / __| |/ / | | | '_ ` _ \| | '_ \
 * \__ \   <| |_| | | | | | | | | | |
 * |___/_|\_\\__, |_| |_| |_|_|_| |_|
 *           |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the MIT License. see <https://opensource.org/licenses/MIT>.
 *
 * @author skymin
 * @link   https://github.com/sky-min
 * @license https://opensource.org/licenses/MIT MIT License
 *
 *   /\___/\
 * 　(∩`・ω・)
 * ＿/_ミつ/￣￣￣/
 * 　　＼/＿＿＿/
 *
 */

declare(strict_types=1);

namespace skymin\InventoryLib\session;

use Closure;
use pocketmine\block\tile\Spawnable;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\convert\RuntimeBlockMapping;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\{BlockActorDataPacket, NetworkStackLatencyPacket, UpdateBlockPacket};
use pocketmine\network\mcpe\protocol\types\{BlockPosition, CacheableNbt};
use skymin\InventoryLib\inventory\BaseInventory;

final class PlayerSession{

	private ?BaseInventory $current = null;

	private ?Closure $waitClosure = null;

	public function __construct(private readonly NetworkSession $network){ }

	public function waitOpenWindow(BaseInventory $inv) : void{
		$waitCount = 2;
		if($this->current !== null){
			$this->sendRealBlock($this->current);
			$waitCount = 8;
		}
		$this->current = $inv;
		$this->wait(function() use ($inv, &$waitCount) : bool{
			if($inv !== $this->current || !$this->network->isConnected()){
				$this->waitClosure = null;
				return true;
			}
			if(--$waitCount === 0){
				$this->waitClosure = null;
				$this->network->getPlayer()->setCurrentWindow($inv);
				return true;
			}
			return false;
		});
	}

	private function sendRealBlock(BaseInventory $current) : void{
		$holder = $current->getHolder();
		$world = $holder->world;
		$vec = $holder->asVector3();
		$blockId = $world->getBlock($vec)->getStateId();
		$nbt = null;
		$tile = $world->getTile($vec);
		if($tile instanceof Spawnable){
			$nbt = $tile->getSerializedSpawnCompound();
		}
		$this->sendBlock($vec, $blockId, $nbt);
		if($current->getTypeInfo()->isDouble()){
			$vec = $holder->add(1, 0, 0);
			$blockId = $world->getBlock($vec)->getStateId();
			$nbt = null;
			$tile = $world->getTile($vec);
			if($tile instanceof Spawnable){
				$nbt = $tile->getSerializedSpawnCompound();
			}
			$this->sendBlock($vec, $blockId, $nbt);
		}
	}

	public function sendBlock(Vector3 $pos, int $blockId, ?CacheableNbt $tile = null) : void{
		$pos = BlockPosition::fromVector3($pos);
		$pk = UpdateBlockPacket::create(
			$pos,
			RuntimeBlockMapping::getInstance()->toRuntimeId($blockId),
			UpdateBlockPacket::FLAG_NETWORK,
			UpdateBlockPacket::DATA_LAYER_NORMAL
		);
		$this->network->sendDataPacket($pk);
		if($tile !== null){
			$pk = BlockActorDataPacket::create($pos, $tile);
			$this->network->sendDataPacket($pk);
		}
	}

	private function wait(Closure $then) : void{
		$this->network->sendDataPacket(NetworkStackLatencyPacket::request(1));
		$this->waitClosure = $then;
	}

	public function onClose(BaseInventory $current) : void{
		$this->sendRealBlock($current);
		$this->current = null;
		$this->waitClosure = null;
	}

	public function closeWindow() : void{
		$current = $this->current;
		if($current !== null){
			$player = $this->network->getPlayer();
			if($current === $player->getCurrentWindow()){
				$player->removeCurrentWindow();
			}else{
				$this->sendRealBlock($current);
				$this->current = null;
				$this->waitClosure = null;
			}
		}
	}

	public function getCurrent() : ?BaseInventory{
		return $this->current;
	}

	public function notify() : void{
		if($this->waitClosure === null) return;
		if(!($this->waitClosure)()){
			$this->network->sendDataPacket(NetworkStackLatencyPacket::request(1));
		}
	}

}
