<?php

namespace Valiant\Managers;

use pocketmine\Player;
use pocketmine\utils\UUID;
use Valiant\Core;

class PlayerMGR {

    private $plugin;
    private $players = [];

    public function __construct(Core $plugin) {
        $this->plugin = $plugin;
    }

    public function getPlayer(Player $player): ?PlayerMGR {
        return $this->getPlayerByUUID($player->getUniqueId());
    }

    public function getPlayerByUUID(UUID $uuid): ?PlayerMGR {
        return $this->players[$uuid->toString()] ?? null;
    }

    public function getPlayerByName(string $name): ?PlayerMGR {
        foreach ($this->players as $player) {
            if (strtolower($player->getName()) === strtolower($name)) return $player;
        }
        return null;
    }

    public function getPlayers(): array {
        return $this->players;
    }
}
