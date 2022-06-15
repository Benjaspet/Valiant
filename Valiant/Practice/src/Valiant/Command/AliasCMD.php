<?php

declare(strict_types=1);

namespace Valiant\Command;

use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;
use pocketmine\utils\Config;
use Valiant\Core;

class AliasCMD extends PluginCommand {

    private $plugin;

    public function __construct(Core $plugin) {
        parent::__construct("alias", $plugin);
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
            $sender->sendMessage("§cPlease provide an online player.");
            return false;
        }
        if ($this->plugin->getServer()->getPlayer($args[0]) === null) {
            $sender->sendMessage("§cPlayer not found.");
            return false;
        }
        $target = $this->plugin->getServer()->getPlayer($args[0]);
        $name = strtolower($args[0]);
        $player = $this->plugin->getServer()->getPlayer($name);
        if ($player instanceof Player) {
            $ip = $player->getPlayer()->getAddress();
            $file = new Config($this->plugin->getDataFolder() . "ipdb/" . $ip . ".txt");
            $names = $file->getAll(true);
            $names = implode(', ', $names);
            $sender->sendMessage("§cListing alternate accounts...");
            $sender->sendMessage("§7" . $names);
            return true;
        }
        return true;
    }
}