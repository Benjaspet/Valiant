<?php

declare(strict_types=1);

namespace Valiant\Duels;

use pocketmine\Player;
use pocketmine\utils\UUID;
use Valiant\Core;

class DuelManager {
    private $plugin;
    private $duels = [];

    public function __construct(Core $plugin) {
        $this->plugin = $plugin;
    }

    public function getDuel(string $id): ?Duel {
        return $this->duels[$id] ?? null;
    }

    public function getDuels(): array {
        return $this->duels;
    }

    public function createDuel(Player $player, string $type, bool $ranked, string $map): Duel {
        $id = UUID::fromRandom()->toString();
        while (isset($this->duels[$id])) $id = UUID::fromRandom()->toString();
        $this->duels[$id] = new Duel($id, [$player->getName()], $type, $ranked, $this->plugin, $map);
        return $this->duels[$id];
    }

    public function deleteDuel(string $id): void
    {
        unset($this->duels[$id]);
    }
}