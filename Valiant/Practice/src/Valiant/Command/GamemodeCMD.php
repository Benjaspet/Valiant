<?php

declare(strict_types=1);

namespace Valiant\Command;

use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;
use Valiant\Core;

class GamemodeCMD extends PluginCommand {

    private $plugin;

    public function __construct(Core $plugin) {
        parent::__construct("gamemode", $plugin);
        $this->plugin = $plugin;
        $this->setAliases(["gm"]);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if (!$sender instanceof Player) {
            return false;
        }
        if (!$sender->hasPermission("valiant.staff")) {
            $sender->sendMessage("§cYou cannot execute this command.");
            return false;
        }
        $this->plugin->getFormUtil()->gamemodeForm($sender);
        return true;
    }
}