<?php

declare(strict_types=1);

namespace Valiant\Command;

use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;
use Valiant\Core;

class KickCMD extends PluginCommand {

    private $plugin;

    public function __construct(Core $plugin) {
        parent::__construct("kick", $plugin);
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if (!$sender instanceof Player) {
            return false;
        }
        if (!$sender->hasPermission("valiant.staff")) {
            $sender->sendMessage("§cYou cannot execute this command.");
            return false;
        }
        if (!isset($args[0])) {
            $this->plugin->getFormUtil()->kickForm($sender);
            return true;
        }
        $target = $this->plugin->getServer()->getPlayer($args[0]);
        if ($target->isOp()) {
            $sender->sendMessage("§cThat player cannot be kicked.");
            return false;
        }
        $target = $this->plugin->getServer()->getPlayer($args[0]);
        if($target->getName() == $sender->getName()){
            $sender->sendMessage("§cYou cannot kick yourself.");
            return false;
        }
        $this->plugin->getFormUtil()->kickForm($sender);
        return true;
    }
}