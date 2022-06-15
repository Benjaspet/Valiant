<?php

declare(strict_types=1);

namespace Valiant\Event\Match\Stage;

abstract class Stage {

    private $match;

    public function __construct(Match $match) {
        $this->match = $match;
        $this->onStart();
    }

    public abstract function onRun(): void;

    public function onStart(): void {}

    public function getMatch(): Match {
        return $this->match;
    }

    public function broadcastMessage(string $message): void {
        foreach($this->match->getMap()->getRegion()->getLevel()->getPlayers() as $player) {
            $this->match->getSession($player)->sendTranslatedMessage($message);
        }
    }

}
