<?php

declare(strict_types=1);

namespace Valiant\Command;

use pocketmine\Player;
use pocketmine\command\PluginCommand;
use pocketmine\command\CommandSender;
use Valiant\Core;

class PingCMD extends PluginCommand{

    private $plugin;

    public function __construct(Core $plugin){
        parent::__construct("ping", $plugin);
        $this->plugin=$plugin;
        $this->setAliases(["ms"]);
    }
    public function execute(CommandSender $player, string $commandLabel, array $args): bool {
        if(!isset($args[0]) and $player instanceof Player){
            $player->sendMessage("§aYour ping: ".$player->getPing()."ms.");
            return true;
        }
        if(isset($args[0]) and $target=$this->plugin->getServer()->getPlayer($args[0])===null){
            $player->sendMessage("§cPlayer not found.");
            return true;
        }
        $target=$this->plugin->getServer()->getPlayer($args[0]);
        if($target instanceof Player){
            $player->sendMessage("§a".$target->getName()."'s ping is ".$target->getPing()."ms.");
        }
        return true;
    }
}