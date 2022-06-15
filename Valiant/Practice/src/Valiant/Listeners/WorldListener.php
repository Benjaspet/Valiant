<?php

declare(strict_types=1);

namespace Valiant\Listeners;

use pocketmine\block\Anvil;
use pocketmine\block\BrewingStand;
use pocketmine\block\BurningFurnace;
use pocketmine\block\Button;
use pocketmine\block\Chest;
use pocketmine\block\CraftingTable;
use pocketmine\block\Door;
use pocketmine\block\EnchantingTable;
use pocketmine\block\EnderChest;
use pocketmine\block\FenceGate;
use pocketmine\block\Furnace;
use pocketmine\block\IronDoor;
use pocketmine\block\IronTrapdoor;
use pocketmine\block\Lever;
use pocketmine\block\Trapdoor;
use pocketmine\block\TrappedChest;
use pocketmine\event\block\BlockBurnEvent;
use pocketmine\event\block\LeavesDecayEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\entity\ExplosionPrimeEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\Bed;
use pocketmine\Player;
use Valiant\Core;

class WorldListener {

    private $plugin;

    public function __construct(Core $plugin) {
        $this->plugin = $plugin;
    }

    public function onSlotChange(InventoryTransactionEvent $event) {
        $transaction = $event->getTransaction();
        $player = $transaction->getSource();
        $level = $player->getLevel()->getFolderName();
        if ($level === $this->plugin->getUtils()->getLobby()) {
            $event->setCancelled();
        }
    }

    public function nullInteract(PlayerInteractEvent $event) {
        $player = $event->getPlayer();
        $b = $event->getBlock();
        $i = $event->getItem();
        if ($player instanceof Player) {
            if ($b instanceof Anvil or $b instanceof Bed or $b instanceof BrewingStand or $b instanceof BurningFurnace or $b instanceof Button or $b instanceof Chest or $b instanceof CraftingTable or $b instanceof Door or $b instanceof EnchantingTable or $b instanceof EnderChest or $b instanceof FenceGate or $b instanceof Furnace or $b instanceof IronDoor or $b instanceof IronTrapDoor or $b instanceof Lever or $b instanceof TrapDoor or $b instanceof TrappedChest) {
                $event->setCancelled(true);
            }
        }
    }

    public function onLeaveDecay(LeavesDecayEvent $event) {
        $block = $event->getBlock();
        $level = $block->getLevel();
        $event->setCancelled();
    }

    public function onBurn(BlockBurnEvent $event) {
        $block = $event->getBlock();
        $level = $block->getLevel()->getName();
        $event->setCancelled();
    }

    public function onPrimedExplosion(ExplosionPrimeEvent $event) {
        $event->setBlockBreaking(false);
    }

    public function onExhaust(PlayerExhaustEvent $event) {
        $player = $event->getPlayer();
        $event->setCancelled();
    }

    public function onArenaDamage(EntityDamageByEntityEvent $event): void {
        $player = $event->getEntity();
        $damager = $event->getDamager();
        $arena = $this->plugin->getDuelHandler()->getArenaByPlayer($player);
        if (!($player instanceof Player) or !($damager instanceof Player)) {
            return;
        }
        if ($arena and $arena->isStarting()) {
            $player->getInventory()->clearAll();
            $event->setCancelled();
        }
    }
}