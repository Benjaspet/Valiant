<?php

namespace Valiant\Task;

use pocketmine\scheduler\Task;
use pocketmine\Player;
use Valiant\Core;
use Valiant\Listeners\PlayerListener;

class PearlTask extends Task {

    private $plugin;
    private $player;
    private $timer = 151; # always set 1 value higher than the duration you want

    public function __construct(Core $plugin, Player $player, int $timer) {
        $this->plugin = $plugin;
        $this->timer = $timer;
        $this->player = $player;
    }

    public function onRun(int $currentTick) {
        $this->timer--;
        if ($this->timer == 151) {
            $percent = floatval($this->timer / 151);
            $this->player->setXpProgress($percent);
            $this->plugin->getScoreboardUtil()->updatePearlLine($this->player, floatval(1 + $this->timer / 10));
        }
        if ($this->timer < 151) {
            $percent = floatval($this->timer / 151);
            $this->player->setXpProgress($percent);
            $this->plugin->getScoreboardUtil()->updatePearlLine($this->player, floatval(1 + $this->timer / 10));
        }
        if ($this->timer <= 0) {
            $this->timer = 0;
            $this->player->setXpProgress(0);
            $this->player->setXpLevel(0);
            $this->plugin->getScoreboardUtil()->updatePearlLine($this->player, 0);
            unset(PlayerListener::$cooldown[$this->player->getName()]);
            $this->plugin->getScheduler()->cancelTask($this->getTaskId());
        }
    }
}