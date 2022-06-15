<?php

declare(strict_types=1);

namespace Valiant;

use MongoDB\Driver\Query;
use pocketmine\event\block\LeavesDecayEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\inventory\CraftItemEvent;
use pocketmine\event\inventory\FurnaceSmeltEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\plugin\PluginDisableEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\event\server\QueryRegenerateEvent;
use pocketmine\item\Item;
use pocketmine\level\Position;
use pocketmine\network\mcpe\protocol\DisconnectPacket;
use pocketmine\Player;
use Valiant\Libs\Query\NetworkQuery;
use Valiant\Libs\SimpleForm;
use Valiant\Query\QueryUtil;

class Listener implements \pocketmine\event\Listener {

    private $plugin;

    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
    }

    function onCraft(CraftItemEvent $event) {
        $event->setCancelled(true);
    }

    function onFurnace(FurnaceSmeltEvent $event) {
        $event->setCancelled(true);
    }

    public function onExhaust(PlayerExhaustEvent $event) {
        $cause = $event->getCause();
        $event->setCancelled(true);
    }

    public function onDeathMessage(PlayerDeathEvent $event) {
        $event->setDeathMessage(null);
    }

    public function onDecay(LeavesDecayEvent $event) {
        $event->setCancelled(true);
    }

    public function onDrop(PlayerDropItemEvent $event) {
        $event->setCancelled(true);
    }

    public function onDisconnectPacket(DataPacketSendEvent $event) {
        $packet = $event->getPacket();
        $player = $event->getPlayer();
        if ($packet instanceof DisconnectPacket and $packet->message === "Internal server error") {
            $packet->message = ("§cYou have encountered a bug.\n§cContact us on Discord: §7https://discord.gg/PBjrx4S9Wa");
            $player->transfer("45.134.8.14", 19132);
        }
    }

    public function onPluginDisable(PluginDisableEvent $event) {
        if ($event->getPlugin()->getName() === "Valiant-Hub") {
            $this->plugin->getServer()->getLogger()->info("Valiant hub core disabled successfully.");
        }
    }

    public function onJoin(PlayerJoinEvent $event) {
        $player = $event->getPlayer();
        $player->setGamemode(2);
        $player->setFood(20);
        $player->setXpProgress(0);
        $player->setHealth(20);
        $player->setXpLevel(0);
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->removeAllEffects();
        $player->setFood(20);
        $event->setJoinMessage("§8[§2+§8] §a" . $player->getName());
        $player->sendMessage("§aWelcome to Valiant, " . $player->getName() . "!");
        $player->sendTitle("§l§cValiant", "§7Practice", 10, 30, 10);
        $this->teleportToHub($player);
        $this->giveHubItem($player);
        $this->plugin->getScoreboardUtil()->setHubScoreboard($player);
    }

    public function onChat(PlayerChatEvent $event) {
        $player = $event->getPlayer();
        $event->setCancelled(true);
        $player->sendMessage("§cYou cannot chat on this server.");
    }

    public function onDamage(EntityDamageByEntityEvent $event) {
        $event->setCancelled(true);
    }

    public function onTransferItemInteract(PlayerInteractEvent $event) {
        $player = $event->getPlayer();
        $item = $event->getItem();
        $action = $event->getAction();
        if ($action == PlayerInteractEvent::RIGHT_CLICK_AIR) {
            if ($player instanceof Player && $item->getCustomName() === "§r§l§cTransfer") {
                $this->transferForm($player);
            }
        }
    }

    public function onDeath(EntityDamageEvent $event) {
        $player = $event->getEntity();
        $cause = $event->getCause();
        switch ($cause) {
            case EntityDamageEvent::CAUSE_VOID:
                if ($player instanceof Player) {
                    $event->setCancelled(true);
                    $this->teleportToHub($player);
                    break;
                }
                break;
            case EntityDamageEvent::CAUSE_ENTITY_ATTACK:
                $event->setCancelled(true);
                break;
        }
    }

    public function onQuery(QueryRegenerateEvent $event) {
        $event->setMaxPlayerCount(50);
    }

    public function transferForm(Player $player): SimpleForm {
        $form = new SimpleForm (function (Player $event, $data) {
            $player = $event->getPlayer();
            if ($data === null) {
                return;
            }
            switch ($data) {
                case 0:
                    $player->transfer("45.134.8.14", 19132);
                    break;
                case 1:
                    break;
            }
        });
        $form->setTitle("§lSERVER SELECTOR");
        $form->setContent("");
        $form->addButton("NA Practice\n" . QueryUtil::getNaPlayerCount() . "/50", 0);
        $form->addButton("Hub\n" . count($this->plugin->getServer()->getOnlinePlayers()) . "/15", 0);
        $player->sendForm($form);
        return $form;
    }

    public function onWhitelistPacket(DataPacketSendEvent $event) {
        $packet = $event->getPacket();
        $player = $event->getPlayer();
        if ($packet instanceof DisconnectPacket and $packet->message === "Server is white-listed") {
            $packet->message = ("§l§cValiant §r§cis currently whitelisted.\n§cJoin our Discord: §7https://discord.gg/PBjrx4S9Wa");
        }
    }

    public function giveHubItem(Player $player) {
        $item = Item::get(Item::COMPASS);
        $item->setCustomName("§r§l§cTransfer");
        $player->getInventory()->setItem(4, $item);
    }

    public function teleportToHub(Player $player) {
        $lobby = $this->plugin->getServer()->getLevelByName("Hub");
        $pos = new Position(282.51, 75.11, 286.46, $lobby);
        $player->teleport($pos);
    }
}