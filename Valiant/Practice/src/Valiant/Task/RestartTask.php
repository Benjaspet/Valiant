<?php

declare(strict_types=1);

namespace Valiant\Task;

use pocketmine\Player;
use pocketmine\scheduler\Task;
use Valiant\Core;

class RestartTask extends Task{

    private $plugin;

    public function __construct(Core $plugin){
        $this->plugin = $plugin;
    }
    public function onRun(int $currentTick):void{
        $count = count($this->plugin->getServer()->getOnlinePlayers());
        if($count > 0) {
            foreach($this->plugin->getServer()->getOnlinePlayers() as $player){
                $player->transfer("45.134.8.14", 19132, "§aServer restarting.");
            }
        } else {
            $this->plugin->getServer()->shutdown();
        }
    }
}