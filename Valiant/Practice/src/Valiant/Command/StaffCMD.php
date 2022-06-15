<?php

declare(strict_types=1);

namespace Valiant\Command;

use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;
use Valiant\Core;

class StaffCMD extends PluginCommand {

    private $plugin;

    public function __construct(Core $plugin) {
        parent::__construct("staff", $plugin);
        $this->plugin = $plugin;
        $this->setAliases(["staffmode"]);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if (!$sender instanceof Player) {
            return false;
        }
        if (!$sender->hasPermission("valiant.staff")) {
            $sender->sendMessage("§cYou cannot execute this command.");
            return false;
        }
        if (isset($args[0])) {
            switch(strtolower($args[0])) {
                case "on":
                    $this->plugin->getKitUtil()->sendKit($sender, "StaffMode");
                    $this->plugin->getStaffUtils()->staffMode($sender, true);
                    $sender->setGamemode(3);
                    break;
                case "off":
                    $sender->setInvisible(false);
                    $this->plugin->getUtils()->teleportToHub($sender);
                    $this->plugin->getKitUtil()->sendKit($sender, "Lobby");
                    $this->plugin->getServer()->broadcastMessage("§8[§2+§8] §a" . $sender->getName());
                    $sender->sendMessage("§aYou are no longer vanished.");
                    $this->plugin->getStaffUtils()->sendStaffNotification("§c" . $sender->getName() . " has unvanished.");
                    $sender->setGamemode(2);
                    if ($sender->isOp() || $sender->hasPermission("valiant.staff")) {
                        $sender->setFlying(false);
                        $sender->setAllowFlight(true);
                    } else {
                        $sender->setFlying(false);
                    }
                    break;
            }
        }
        return true;
    }
}
