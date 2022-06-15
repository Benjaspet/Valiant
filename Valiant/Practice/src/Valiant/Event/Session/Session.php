<?php

declare(strict_types=1);

namespace Valiant\Event\Session;

use pocketmine\Player;
use Valiant\Event\Match\Match;
use Valiant\Event\Scoreboard\SumoScoreboard;

class Session {

    private $manager;
    private $player;
    private $match = null;
    private $scoreboard;

    public function __construct(SessionManager $manager, Player $player) {
        $this->manager = $manager;
        $this->player = $player;
        $this->scoreboard = new SumoScoreboard($this);
    }

    public function getManager(): SessionManager {
        return $this->manager;
    }

    public function getPlayer(): Player {
        return $this->player;
    }

    public function getMatch(): ?Match {
        return $this->match;
    }

    public function hasMatch(): bool {
        return $this->match != null;
    }

    public function setMatch(?Match $match): void {
        $this->match = $match;
    }

    public function prepareForSumo(): void {
        if(!$this->hasMatch()) {
            return;
        }
        $this->player->setGamemode(Player::SURVIVAL);
        $this->teleportToSumoSpawn();
    }

    public function teleportToSumoWorld(): void {
        if (!$this->hasMatch()) {
            return;
        }
        $level = $this->match->getMap()->getRegion()->getLevel();

        if ($this->player->getLevel()->getName() != $level->getName()) {
            $this->player->teleport($level->getSafeSpawn());
        }
    }

    public function teleportToSumoSpawn(): void {
        if (!$this->hasMatch()) {
            return;
        }
        $this->teleportToSumoWorld();
        $this->player->teleport($this->match->getMap()->getSpawnPosition());
    }

    public function sendSumoScoreboard(Player $player): void {
        $this->scoreboard->updateScoreboard($player);
    }

}
