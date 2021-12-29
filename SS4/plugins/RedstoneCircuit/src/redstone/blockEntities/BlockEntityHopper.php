<?php

namespace redstone\blockEntities;

use pocketmine\entity\object\ItemEntity;

use pocketmine\inventory\InventoryHolder;

use pocketmine\nbt\tag\CompoundTag;

use pocketmine\tile\Container;
use pocketmine\tile\ContainerTrait;
use pocketmine\tile\Nameable;
use pocketmine\tile\NameableTrait;
use pocketmine\tile\Spawnable;

use pocketmine\math\AxisAlignedBB;


use redstone\blocks\BlockHopper;

use redstone\inventories\HopperInventory;

use redstone\utils\Facing;

class BlockEntityHopper extends Spawnable implements InventoryHolder, Container, Nameable {
    use NameableTrait {
        addAdditionalSpawnData as addNameSpawnData;
    }
    use ContainerTrait;

    protected $inventory;

    protected $transferCooldown = 0;

    protected $area;
    
    protected function readSaveData(CompoundTag $nbt) : void {
        if ($nbt->hasTag("transferCooldown")) {
            $this->transferCooldown = $nbt->getInt("transferCooldown");
        }

        $this->inventory = new HopperInventory($this);
        $this->loadName($nbt);
		$this->loadItems($nbt);
        $this->scheduleUpdate();

        $this->area = new AxisAlignedBB($this->x, $this->y + 1, $this->z, $this->x + 1, $this->y + 2, $this->z + 1);
    }

    protected function writeSaveData(CompoundTag $nbt) : void {
        $nbt->setInt("transferCooldown", $this->transferCooldown);

        $this->saveName($nbt);
		$this->saveItems($nbt);
    }

    public function getDefaultName() : string {
        return "Hopper";
    }
    
    public function getInventory() {
        return $this->inventory;
    }

    public function getRealInventory() {
        return $this->inventory;
    }

    protected function addAdditionalSpawnData(CompoundTag $nbt) : void {
        $this->addNameSpawnData($nbt);
    }
    
    public function onUpdate() : bool {
        if ($this->closed) {
            return false;
        }

        $block = $this->getBlock();
        if (!($block instanceof BlockHopper)) {
            return false;
        }

        if ($block->isRedstoneLocked()) {
            return true;
        }
        
        $entities = $this->getLevel()->getNearbyEntities($this->area);
        for ($i = 0; $i < count($entities); ++$i) {
            $entity = $entities[$i];
            if (!($entity instanceof ItemEntity)) {
                continue;
            }

            $item = $entity->getItem();
            if (!$this->getInventory()->canAddItem($item)) {
                continue;
            }

            $this->getInventory()->addItem($item);
            $entity->kill();
        }

        $this->transferCooldown--;
        if ($this->transferCooldown > 0) {
            return true;
        }

        $change = false;

        $side = $this->getSide($block->getFace());
        $tile = $this->getLevel()->getTile($side);
        if ($tile != null && $tile instanceof InventoryHolder) {
            $hopper = $this->getInventory();
            $inventory = $tile->getInventory();
            for ($i = 0; $i < $hopper->getSize(); ++$i) {
                $item = $hopper->getItem($i);
                if ($item->getId() == 0) {
                    continue;
                }

                $cloneItem = clone $item;
                $cloneItem->setCount(1);
                if (!$inventory->canAddItem($cloneItem)) {
                    continue;
                }

                $inventory->addItem($cloneItem);
                $item->setCount($item->getCount() - 1);
                $hopper->setItem($i, $item);
                $change = true;
                break;
            }
        }

        $side = $this->getSide(Facing::UP);
        $tile = $this->getLevel()->getTile($side);
        if ($tile != null && $tile instanceof InventoryHolder) {
            $hopper = $this->getInventory();
            $inventory = $tile->getInventory();
            for ($i = 0; $i < $inventory->getSize(); ++$i) {
                $item = $inventory->getItem($i);
                if ($item->getId() == 0) {
                    continue;
                }

                $cloneItem = clone $item;
                $cloneItem->setCount(1);
                if (!$hopper->canAddItem($cloneItem)) {
                    continue;
                }

                $hopper->addItem($cloneItem);
                $item->setCount($item->getCount() - 1);
                $inventory->setItem($i, $item);
                $change = true;
                break;
            }
        }

        if ($change) {
            $this->transferCooldown = 8;
        }
        return true;
    }
}