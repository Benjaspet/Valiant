<?php

declare(strict_types=1);

namespace Valiant\Command;

use pocketmine\Player;
use pocketmine\command\PluginCommand;
use pocketmine\command\CommandSender;
use pocketmine\level\Position;
use Valiant\Core;

class TpAllCMD extends PluginCommand{

    private $plugin;

    public function __construct(Core $plugin){
        parent::__construct("tpall", $plugin);
        $this->plugin=$plugin;
        $this->setPermission("valiant.tpall");
    }
    public function execute(CommandSender $player, string $commandLabel, array $args){
        if(!$player->hasPermission("valiant.tpall")){
            $player->sendMessage("§cYou cannot execute this command.");
            return;
        }
        foreach($this->plugin->getServer()->getOnlinePlayers() as $online){
            if($online->getName()!=$player->getName() and count($this->plugin->getServer()->getOnlinePlayers()) > 1){
                $online->teleport($this->plugin->getServer()->getPlayer($player->getName())->getLocation());
            }
        }
        $player->sendMessage("§aAll players have been teleported to you.");
        $this->plugin->getServer()->broadcastMessage("§aAll players have been teleported to " . $player->getName());

    }
}