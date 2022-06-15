<?php

declare(strict_types=1);

namespace Valiant\Command;

use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;
use Valiant\Core;

class PDataCMD extends PluginCommand {

    private $plugin;

    public function __construct(Core $plugin) {
        parent::__construct("pinfo", $plugin);
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if ($sender instanceof Player) {
            if (!$sender->hasPermission("valiant.staff")) {
                $sender->sendMessage("§cYou cannot execute this command.");
                return false;
            }
            if (!isset($args[0])) {
                $sender->sendMessage("§cPlease provide a player name.");
                return false;
            }
            if ($this->plugin->getServer()->getPlayer($args[0]) === null) {
                $sender->sendMessage("§cThat player is not online");
                return false;
            }
            $target = $this->plugin->getServer()->getPlayer($args[0]);
            if ($target->isOp()) {
                $op = "true";
            } else {
                $op = "false";
            }
            $name = $target->getDisplayName();
            $ip = $this->plugin->getUtils()->hashIp((string) $target->getAddress());
            $ping = $target->getPing();
            $level = $target->getLevel()->getFolderName();
            $xuid = $target->getXuid();
            $cps = $this->plugin->getClickUtil()->getCps($target);
            $sender->sendMessage("§7==== §c§l" . $name . " §r§7====");
            $sender->sendMessage("§cOP: §7" . $op . " §cPing: §7" . $ping . "ms");
            $sender->sendMessage("§cIP: §7" . $ip);
            $sender->sendMessage("§cCPS: §7" . $cps);
            $sender->sendMessage("§cX-UID: §7" . $xuid);
            $sender->sendMessage("§cCurrent world: §7" . $level);
        }
        return true;
    }
}