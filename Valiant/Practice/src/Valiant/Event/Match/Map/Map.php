<?php

declare(strict_types=1);

namespace Valiant\Event\Match\Map;

use pocketmine\math\Vector3;
use Valiant\Event\Utils\Region;

class Map {

    private $manager;
    private $identifier;
    private $name;
    private $author;
    private $spawnPosition;
    private $firstDuelPosition;
    private $secondDuelPosition;
    private $region;

    public function __construct(
        MapManager $manager,
        string $identifier,
        string $name,
        string $author,
        Vector3 $spawnPosition,
        Vector3 $firstDuelPosition,
        Vector3 $secondDuelPosition,
        Region $region
    ) {
        $this->manager = $manager;
        $this->identifier = $identifier;
        $this->name = $name;
        $this->author = $author;
        $this->spawnPosition = $spawnPosition;
        $this->firstDuelPosition = $firstDuelPosition;
        $this->secondDuelPosition = $secondDuelPosition;
        $this->region = $region;
    }

    public function getManager(): MapManager {
        return $this->manager;
    }

    public function getIdentifier(): string {
        return $this->identifier;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getAuthor(): string {
        return $this->author;
    }

    public function getSpawnPosition(): Vector3 {
        return $this->spawnPosition;
    }

    public function getFirstDuelPosition(): Vector3 {
        return $this->firstDuelPosition;
    }

    public function getSecondDuelPosition(): Vector3 {
        return $this->secondDuelPosition;
    }

    public function getRegion(): Region {
        return $this->region;
    }
}
