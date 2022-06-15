<?php

declare(strict_types=1);

namespace Valiant\Task;

use pocketmine\Player;
use pocketmine\scheduler\Task;
use Valiant\Core;

class CombatTask extends Task {

    private $plugin;

    public function __construct(Core $plugin) {
        $this->plugin = $plugin;
    }

    public function onRun(int $currentTick): void {
        foreach($this->plugin->getPlayerUtil()->taggedPlayer as $name => $time) {
            $player = $this->plugin->getServer()->getPlayer($name);
            $time--;
            if ($player instanceof Player and $this->plugin->getPlayerUtil()->isTagged($player)) {
                $player->setXpLevel($time);
                $this->plugin->getScoreboardUtil()->updateMainLineCombat($player, $time);
            }
            if ($time <= 0) {
                if ($player instanceof Player) {
                    $this->plugin->getPlayerUtil()->setTagged($player, false);
                    $this->plugin->getScoreboardUtil()->updateMainLineCombat($player, 0);
                    $player->setXpLevel(0);
                    $player->sendMessage("§aYou are no longer in combat.");
                    return;
                }
            }
            $this->plugin->getPlayerUtil()->taggedPlayer[$name]--;
        }
    }
}