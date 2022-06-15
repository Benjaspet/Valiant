<?php

namespace Valiant\Listeners;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\LeavesDecayEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\plugin\PluginDisableEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\event\server\QueryRegenerateEvent;
use pocketmine\network\mcpe\protocol\DisconnectPacket;
use pocketmine\Player;
use Valiant\Core;

class ServerListener implements Listener {

    private $plugin;

    public function __construct(Core $plugin) {
        $this->plugin = $plugin;
    }

    public function onLoad() : void {
        foreach ($this->plugin->getServer()->getLevels() as $level) {
            $this->plugin->getServer()->loadLevel($level->getFolderName());
            $level->setTime(7000);
            $level->stopTime();

        }
    }

    public function onDecay(LeavesDecayEvent $event) {
        $event->setCancelled(true);
    }

    public function onDisconnectPacket(DataPacketSendEvent $event) {
        $packet = $event->getPacket();
        $player = $event->getPlayer();
        if ($packet instanceof DisconnectPacket and $packet->message === "Internal server error") {
            $packet->message = ("§cYou have encountered a bug.\n§cContact us on Discord: §7" . Core::DISCORD);
            foreach ($this->plugin->getServer()->getOnlinePlayers() as $online) {
                if ($online->hasPermission("valiant.staff")) {
                    $online->sendPopup("§c§lWarning: internal server error!");
                }
            }
        }
        if ($packet instanceof DisconnectPacket and $packet->message === "You are banned") {
            $packet->message = ("§cYou have been suspended from Valiant.\n§cBanned by: §7CONSOLE\n§cReason: §7unfair advantage or abuse\n§cAppeal: §7" . Core::DISCORD);
        }
        if ($packet instanceof DisconnectPacket and $packet->message === "You are banned") {
            $packet->message = ("§c§lValiant§r§c is whitelisted at the moment.\n§cPlease try logging in again later.\n§cJoin the Discord for updates: §7" . Core::DISCORD);
        }
        if ($packet instanceof DisconnectPacket and $packet->message === "You are banned") {
            $packet->message = ("§c§lValiant§r§c is full at the moment.\n§cPlease try logging in again later.\n§cJoin the Discord for updates: §7" . Core::DISCORD);
        }
    }

    public function onPlace(BlockPlaceEvent $event) {
        if (!$event->getPlayer()->isOp()) {
            $event->getPlayer()->sendPopup("§cYou cannot place blocks.");
            $event->setCancelled(true);
        }
    }

    public function onBreak(BlockBreakEvent $event) {
        if (!$event->getPlayer()->isOp()) {
            $event->getPlayer()->sendPopup("§cYou cannot break blocks.");
            $event->setCancelled(true);
        }
    }

    public function onPluginDisable(PluginDisableEvent $event) {
        if ($event->getPlugin()->getName() === "Valiant") {
            $this->plugin->getServer()->getLogger()->info("Valiant Practice core disabled successfully.");
        }
    }

    public function onDisable() {
        $this->plugin->saveData();
        $this->plugin->getPlayerListener()->combat = [];
    }

    public function onQuery(QueryRegenerateEvent $event) {
        $eventName = $event->getEventName();
        $serverName = $event->getServerName();
        $shortQuery = $event->getShortQuery();
        $longQuery = $event->getLongQuery();
        $world = $event->getWorld();
        $online = $event->getPlayerCount();
        $maxOnline = $event->getMaxPlayerCount();
        $list = $event->getPlayerList();
        foreach ($this->plugin->getServer()->getOnlinePlayers() as $player) {
            if ($player instanceof Player) {
                if ($player->isPermissionSet("vityaz.hub")) {
                    $player->kick("There was an error. Please join back.", false, "[-] " . $player->getDisplayName());
                }
            }
        }
    }
}
