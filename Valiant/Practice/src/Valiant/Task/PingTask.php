<?php

declare(strict_types=1);

namespace Valiant\Task;

use pocketmine\scheduler\Task;
use Valiant\Core;

class PingTask extends Task {

    private $plugin;

    public function __construct(Core $plugin) {
        $this->plugin = $plugin;
    }

    public function onRun(int $currentTick) : void {
        foreach ($this->plugin->getServer()->getOnlinePlayers() as $online) {
            $this->plugin->getScoreboardUtil()->updatePingLine($online);
            $this->plugin->getScoreboardUtil()->updateMainLineOnlinePlayers($online);
        }
    }
}
