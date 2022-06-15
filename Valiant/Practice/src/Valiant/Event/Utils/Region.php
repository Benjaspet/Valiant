<?php

declare(strict_types=1);

namespace Valiant\Event\Utils;

use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use Valiant\Event\Utils\Area;

class Region extends Area {

    protected $level;

    public function __construct(Level $level, Vector3 $firstPosition, Vector3 $secondPosition) {
        $this->level = $level;
        parent::__construct($firstPosition, $secondPosition);
    }

    public function getLevel(): Level {
        return $this->level;
    }

    public function setLevel(Level $level): void {
        $this->level = $level;
    }

    public function inside(Vector3 $position): bool {
        if($position instanceof Position) {
            return parent::inside($position) and $this->level === $position->level;
        }
        return parent::inside($position);
    }

}
