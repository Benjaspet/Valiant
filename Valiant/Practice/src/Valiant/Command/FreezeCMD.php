<?php

declare(strict_types=1);

namespace Valiant\Command;

use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;
use Valiant\Core;

class FreezeCMD extends PluginCommand {

    private $plugin;

    public function __construct(Core $plugin) {
        parent::__construct("freeze", $plugin);
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if (!$sender->hasPermission("valiant.staff")) {
            $sender->sendMessage("§cYou cannot execute this command.");
            return false;
        }
        if (!$sender instanceof Player) {
            return false;
        }
        if (!isset($args[0])) {
            $this->plugin->getFormUtil()->freezeForm($sender);
            return false;
        }
        if ($this->plugin->getServer()->getPlayer($args[0]) === null) {
            $sender->sendMessage("§cPlayer not found.");
            return false;
        }
        $target = $this->plugin->getServer()->getPlayer($args[0]);
        if ($target->isOp()) {
            $sender->sendMessage("§cYou cannot freeze an admin.");
            return false;
        }
        $this->plugin->getStaffUtils()->freezePlayer($target, $sender);
        return true;
    }
}