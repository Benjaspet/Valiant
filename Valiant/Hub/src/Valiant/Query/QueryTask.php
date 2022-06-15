<?php

declare(strict_types=1);

namespace Valiant\Query;

use pocketmine\scheduler\Task;
use Valiant\Main;

class QueryTask extends Task {

    private $plugin;

    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
    }

    public function onRun(int $currentTick) : void {
        foreach ($this->plugin->getServer()->getOnlinePlayers() as $online) {
            $this->plugin->getScoreboardUtil()->updatePingLine($online);
        }
    }
}