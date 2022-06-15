<?php

declare(strict_types=1);

namespace Valiant\Command;

use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;
use Valiant\Core;

class HubCMD extends PluginCommand {

    private $plugin;

    public function __construct(Core $plugin) {
        parent::__construct("hub", $plugin);
        $this->plugin = $plugin;
        $this->setAliases(["lobby", "spawn"]);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if (!$sender instanceof Player) {
            return;
        }
        $this->plugin->getUtils()->teleportToHub($sender);
        $this->plugin->getKitUtil()->sendKit($sender, "Lobby");
        if ($sender->isOp() || $sender->hasPermission("valiant.staff")) {
            $sender->setFlying(true);
            $sender->setAllowFlight(true);
        } else {
            $sender->setFlying(false);
        }
    }
}
