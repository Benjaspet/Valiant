<?php

declare(strict_types=1);

namespace Valiant\Command;

use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;
use Valiant\Core;

class ArenaCMD extends PluginCommand {

    private $plugin;

    public function __construct(Core $plugin) {
        parent::__construct("arena", $plugin);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if (!$sender->isOp()) {
            if (!$sender instanceof Player) return false;
            $sender->sendMessage("§cYou cannot execute this command.");
            return false;
        }
        if (count($args) !== 2) {
            $sender->sendMessage("§cUsage: §7/arena {name} {type} {kit}");
            return false;
        }
        if (!in_array($args[1], $this->plugin->getKits()->getAll(true))) {
            $sender->sendMessage("§cThe " . $args[1] . " kit does not exist.");
            return false;
        }
        $senderName = $sender->getName();
        if ($sender instanceof Player) {
            $info = array(
                "spawns" => [],
                "kit" => $args[2],
                "type" => strtolower($args[1]),
                "world" => $sender->getLevel()->getFolderName(),
                "needed" => "none"
            );
            $this->plugin->getWorldListener()->setspawns[$senderName] = [(string)$args[0], 2];
            $this->plugin->getDuelHandler()->getArenasConfig()->setNested($args[0], $info);
            $this->plugin->getDuelHandler()->getArenasConfig()->save();
            $sender->sendMessage("§aPlease right-click to set the first spawn of arena " . $args[0] . ".");
        }
        return true;
    }
}
