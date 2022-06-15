<?php

declare(strict_types=1);

namespace Valiant\Task;

use pocketmine\Player;
use pocketmine\scheduler\Task;
use Valiant\Core;

class ChatTask extends Task{

    private $plugin;
    public $player;

    private $timer = 4;

    public function __construct(Core $plugin, Player $player){
        $this->plugin=$plugin;
        $this->player = $player;
    }

    public function onRun(int $currentTick):void{
        $this->timer--;
        switch($this->timer){
            case 3:
                $this->plugin->getUtils()->setChatCooldown($this->player, true);
                break;
            case 0:
                $this->plugin->getUtils()->setChatCooldown($this->player, false);
                $this->plugin->getScheduler()->cancelTask($this->getTaskId());
                break;
        }
    }
}
