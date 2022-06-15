<?php

declare(strict_types=1);

namespace Valiant\Command;

use pocketmine\Player;
use pocketmine\command\PluginCommand;
use pocketmine\command\CommandSender;
use Valiant\Core;
use Valiant\Utils\VUtils;

class FlyCMD extends PluginCommand {

    private $plugin;

    public function __construct(Core $plugin){
        parent::__construct("fly", $plugin);
        $this->plugin=$plugin;
        $this->setPermission("valiant.fly");
    }

    public function execute(CommandSender $player, string $commandLabel, array $args): bool {
        if (!$player->hasPermission("valiant.fly")){
            $player->sendMessage("§cYou cannot execute this command.");
            return false;
        }
        if ($player instanceof Player) {
            if (!$player->getLevel()->getFolderName() === VUtils::LOBBY) {
                $player->sendMessage("§cYou cannot fly in this area.");
                return false;
            }
        }
        if ($player->getAllowFlight() === false){
            $player->setFlying(true);
            $player->setAllowFlight(true);
            $player->sendMessage("§aYou enabled flight.");
        } else {
            $player->setFlying(false);
            $player->setAllowFlight(false);
            $player->sendMessage("§aYou disabled flight.");
        }
        return true;
    }
}