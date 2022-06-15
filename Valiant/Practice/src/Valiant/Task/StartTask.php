<?php

namespace Valiant\Task;

use pocketmine\command\ConsoleCommandSender;
use pocketmine\Player;
use pocketmine\scheduler\Task;
use Valiant\Core;

class StartTask extends Task {

    private $plugin;
    private $timer = 16;

    public function __construct(Core $plugin) {
        $this->plugin = $plugin;
    }

    public function onRun(int $currentTick) {
        $this->timer--;
        if ($this->timer == 16) {
            foreach ($this->plugin->getServer()->getOnlinePlayers() as $player) {
                if ($player instanceof Player && $player->getLevel()->getFolderName() === "Sumo-Event") {
                    $player->sendPopup("§aEvent starting in " . $this->timer . "...");
                }
            }
        }
        if ($this->timer < 16) {
            foreach ($this->plugin->getServer()->getOnlinePlayers() as $player) {
                if ($player instanceof Player && $player->getLevel()->getFolderName() === "Sumo-Event") {
                    $player->sendPopup("§aEvent starting in " . $this->timer . "...");
                }
            }
        }
        if ($this->timer <= 0) {
            $this->plugin->getServer()->dispatchCommand(new ConsoleCommandSender(), "event round");
            $this->plugin->getScheduler()->cancelTask($this->getTaskId());
        }
    }
}
