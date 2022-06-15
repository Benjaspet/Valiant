<?php

declare(strict_types=1);

namespace Valiant\Event\Session;

use pocketmine\Player;
use Valiant\Core;
use Valiant\Event\SumoEvent;

class SessionManager {

    private $plugin;
    private $sessions = [];

    public function __construct(Core $plugin) {
        $this->plugin = $plugin;
        $plugin->getServer()->getPluginManager()->registerEvents(new SessionListener($this), $plugin);
    }

    public function getPlugin(): Core {
        return $this->plugin;
    }

    public function getSessions(): array {
        return $this->sessions;
    }

    public function getSession(Player $player): ?Session {
        return $this->sessions[strtolower($player->getName())] ?? null;
    }

    public function openSession(Player $player): void {
        if(!isset($this->sessions[$username = strtolower($player->getName())])) {
            $this->sessions[$username] = new Session($this, $player);
        }
    }

    public function closeSession(Player $player): void {
        unset($this->sessions[strtolower($player->getName())]);
    }
}
