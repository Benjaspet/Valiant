<?php

declare(strict_types=1);

namespace Valiant\Event\Match\Stage;

use pocketmine\Server;
use Valiant\Event\Match\Match;

class PlayingStage extends Stage {

    private $countdown = 6;
    private $versus;
    private $fighting = false;

    public function __construct(Match $match) {
        parent::__construct($match);
    }

    public function getVersus(): Versus {
        return $this->versus;
    }

    public function isFighting(): bool {
        return $this->fighting;
    }

    public function setFighting(bool $fighting = true): void {
        $this->fighting = $fighting;
    }

    public function onRun(): void {
        $match = $this->getMatch();
        $logger = Server::getInstance()->getLogger();
        if ($this->countdown <= 0) {
            $logger->info("Starting versus");
            $this->setFighting();
            $match->teleportVersusPlayers($this->versus);
            $match->getTournament()->addRound();;
        } else if (!$this->fighting) {
            $logger->info("Countdown:  . $this->countdown");
            $this->countdown--;
        }

        if($this->fighting and $this->countdown = 6) {
            $logger->info("Fighting!");
            foreach($this->versus->getPlayers() as $player) {
                if(!$match->getMap()->getRegion()->inside($player)) {
                    $logger->info($player->getName() . " is not in the area!");
                    $logger->info(
                        $player->getName() . " position: " .
                        $player->getFloorX() . ", " . $player->getFloorY() . ", " . $player->getFloorZ()
                    );
                    $match->removePlayer($player);
                    $this->updateVersus();
                    return;
                }
            }
            $this->countdown = 6;
        }
    }

    public function onStart(): void {
        $match = $this->getMatch();
        $match->preparePlayers();
        $match->getTournament()->updateStartingPlayers();
        $this->updateVersus();
    }

    public function updateVersus(): void {
        $logger = Server::getInstance()->getLogger();
        $match = $this->getMatch();
        $tournament = $match->getTournament();
        $players = $tournament->getPlayers();
        $logger->info("Executed updateVersus()");
        if (count($players) == 1) {
            $logger->info("New winner");
            foreach ($players as $player) {
                $match->setStage(new WinningStage($match, $player));
                return;
            }
        }
        $match->checkVersus();
        $this->setFighting(false);
        $this->versus = $tournament->getAvailableVersus();
        if (!$this->versus->hasPlayers()) {
            $logger->info("The versus doesnt have players");
            // $match->checkVersus();
            $this->updateVersus();
            return;
        }
    }
}
