<?php

declare(strict_types=1);

namespace Valiant\Command;

use pocketmine\Player;
use pocketmine\command\PluginCommand;
use pocketmine\command\CommandSender;
use Valiant\Main;

class PingCMD extends PluginCommand{

    private $plugin;

    public function __construct(Main $plugin){
        parent::__construct("ping", $plugin);
        $this->plugin=$plugin;
        $this->setAliases(["ms"]);
    }
    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if (!isset($args[0]) and $sender instanceof Player) {
            $sender->sendMessage("§aYour ping: " . $sender->getPing() . "ms.");
            return true;
        }
        if (!$sender instanceof Player) {
            $sender->sendMessage("§cRun this command as a player.");
            return true;
        }
        if (isset($args[0]) and $target = $this->plugin->getServer()->getPlayer($args[0]) === null) {
            $sender->sendMessage("§cPlayer not found.");
            return true;
        }
        $target = $this->plugin->getServer()->getPlayer($args[0]);
        if ($target instanceof Player) {
            $sender->sendMessage("§a" . $target->getName() . "'s ping: " . $target->getPing() . "ms.");
        }
        return true;
    }
}