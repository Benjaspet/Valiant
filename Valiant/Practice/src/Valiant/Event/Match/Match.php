<?php

declare(strict_types=1);

namespace Valiant\Event\Match;

use pocketmine\Player;
use Valiant\Event\Match\Map\Map
use Valiant\Event\Match\Stage;
use Sumo\match\stage\WaitingStage;
use Sumo\match\tournament\Tournament;
use Sumo\match\tournament\Versus;
use Sumo\session\Session;

class Match {
    
    private $manager;
    private $map;
    private $stage;
    private $tournament;

    public function __construct(MatchManager $manager, Map $map) {
        $this->manager = $manager;
        $this->map = $map;

        $this->reset();
    }

    public function getManager(): MatchManager {
        return $this->manager;
    }

    public function getMap(): Map {
        return $this->map;
    }

    public function getStage(): ?Stage {
        return $this->stage;
    }

    public function getTournament(): Tournament {
        return $this->tournament;
    }

    public function getSession(Player $player): ?Session {
        return $this->manager->getPlugin()->getSessionManager()->getSession($player);
    }

    public function hasStage(): bool {
        return $this->stage != null;
    }

    public function setStage(?Stage $stage): void {
        $this->stage = $stage;
    }

    public function addPlayer(Player $player): void {
        $this->tournament->addPlayer($player);

        $this->getSession($player)->setMatch($this);
        $this->preparePlayer($player);
    }

    public function removePlayer(Player $player): void {
        $this->tournament->removePlayer($player);

        $this->getSession($player)->setMatch(null);
    }

    private function preparePlayer(Player $player): void {
        $this->getSession($player)->prepareForSumo();
    }

    public function preparePlayers(): void {
        foreach($this->tournament->getPlayers() as $player) {
            $this->preparePlayer($player);
        }
    }

    public function teleportVersusPlayers(Versus $versus): void {
        foreach($versus->getPlayers() as $player) {
            $this->getSession($player)->teleportToSumoWorld();
        }

        $versus->getFirstPlayer()->teleport($this->map->getFirstDuelPosition());
        $versus->getSecondPlayer()->teleport($this->map->getSecondDuelPosition());
    }

    public function checkVersus(): void {
        if(count($this->tournament->getVersus()) <= 0) {
            $this->manager->getPlugin()->getLogger()->info("Resetting versus! Checking()");
            $this->tournament->updateVersus();
        }
    }

    public function startTimer(): void {
        $this->stage = new WaitingStage($this);
    }

    public function start(): void {
        if($this->stage instanceof WaitingStage) {
            $this->stage->setCountdown(16);
        }
    }

    public function reset(): void {
        foreach($this->map->getRegion()->getLevel()->getPlayers() as $player) {
            $player->teleport($this->manager->getPlugin()->getServer()->getDefaultLevel()->getSafeSpawn());
        }
        $this->stage = null;
        $this->tournament = new Tournament();
    }
}
