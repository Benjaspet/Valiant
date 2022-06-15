<?php

declare(strict_types=1);

namespace Valiant\Task;

use pocketmine\entity\Entity;
use pocketmine\scheduler\Task;
use Valiant\Core;

class EntityKillTask extends Task {

    private $entity;
    private $plugin;

    public function __construct(Core $plugin, Entity $entity) {
        $this->plugin = $plugin;
        $this->entity = $entity;
    }

    public function onRun(int $currentTick) {
        if (!$this->entity->isClosed()) {
            $this->entity->close();
        }
    }
}
