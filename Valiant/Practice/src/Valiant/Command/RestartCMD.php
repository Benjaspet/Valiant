<?php

declare(strict_types=1);

namespace Valiant\Command;

use pocketmine\Player;
use pocketmine\command\PluginCommand;
use pocketmine\command\CommandSender;
use Valiant\Core;
use Valiant\Task\RestartTask;

class RestartCMD extends PluginCommand{

    private $plugin;

    public function __construct(Core $plugin){
        parent::__construct("restart", $plugin);
        $this->plugin=$plugin;
        $this->setPermission("valiant.admin");
    }
    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if (!$sender->isOp()){
            $sender->sendMessage("§cYou cannot execute this command.");
            return false;
        }
        $this->plugin->getServer()->broadcastMessage("§aValiant will now preform a restart.");
        $this->plugin->getScheduler()->scheduleDelayedRepeatingTask(new RestartTask($this->plugin), 60, 1);
        return true;
    }
}