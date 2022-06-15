<?php

declare(strict_types=1);

namespace Valiant\Event\Match\Map;

use pocketmine\level\Level;
use pocketmine\math\Vector3;
use Valiant\Core;
use Valiant\Event\Match\Map\Map;
use Valiant\Event\SumoEvent;
use Valiant\Event\Utils\Region;

class MapManager {

    private $plugin;
    private $maps = [];

    public function __construct(Core $plugin) {
        $this->plugin = $plugin;
        $this->loadDefaultMaps();
    }

    public function getPlugin(): Core {
        return $this->plugin;
    }

    public function getMaps(): array {
        return $this->maps;
    }

    public function getMapLevel(string $identifier): ?Level {
        $server = $this->plugin->getServer();
        $server->loadLevel($identifier);
        $level = $server->getLevelByName($identifier);
        if($level == null) {
            $server->getLogger()->critical("Map $identifier could NOT be loaded.");
        }
        $level->setAutoSave(false);
        return $level;
    }

    public function loadMap(
        string $identifier,
        string $name,
        string $author,
        Vector3 $spawnPosition,
        Vector3 $firstDuelPosition,
        Vector3 $secondDuelPosition,
        Region $region
    ) {
        if(isset($this->maps[$identifier])) {
            $this->plugin->getLogger()->critical("Couldn't load the map $identifier because it already exists.");
            return;
        }
        $this->maps[$identifier] = new Map(
            $this,
            $identifier,
            $name,
            $author,
            $spawnPosition,
            $firstDuelPosition,
            $secondDuelPosition,
            $region
        );
    }

    private function loadDefaultMaps(): void {
        $this->loadMap(
            "Sumo-Event", "?", "?",
            new Vector3(288, 33, 254),
            new Vector3(256, 27, 250),
            new Vector3(256, 27, 262),
            new Region(
                $this->getMapLevel("Sumo-Event"),
                new Vector3(241, 23, 273),
                new Vector3(273, 17, 243)
            )
        );
    }
}