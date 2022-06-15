<?php

declare(strict_types=1);

namespace Valiant\Event\Utils;

use pocketmine\math\Vector3;

class Area {

    protected $firstVector3;
    protected $secondVector3;

    public function __construct(Vector3 $firstPosition, Vector3 $secondPosition) {
        $this->firstVector3 = $firstPosition;
        $this->secondVector3 = $secondPosition;
    }

    public function getFirstVector3(): Vector3 {
        return $this->firstVector3;
    }

    public function getSecondVector3(): Vector3 {
        return $this->secondVector3;
    }

    public function getMaxX(): int {
        return max($this->firstVector3->getFloorX(), $this->secondVector3->getFloorX());
    }

    public function getMinX(): int {
        return min($this->firstVector3->getFloorX(), $this->secondVector3->getFloorX());
    }

    public function getMaxY(): int {
        return max($this->firstVector3->getY(), $this->secondVector3->getY());
    }

    public function getMinY(): int {
        return min($this->firstVector3->getFloorY(), $this->secondVector3->getFloorY());
    }

    public function getMaxZ(): int {
        return max($this->firstVector3->getFloorZ(), $this->secondVector3->getFloorZ());
    }

    public function getMinZ(): int {
        return min($this->firstVector3->getFloorZ(), $this->secondVector3->getFloorZ());
    }

    public function setFirstVector3(Vector3 $firstVector3): void {
        $this->firstVector3 = $firstVector3;
    }

    public function setSecondVector3(Vector3 $secondVector3): void {
        $this->secondVector3 = $secondVector3;
    }

    public function inside(Vector3 $position): bool {
        if($position->x >= $this->getMinX() and $position->x <= $this->getMaxX() and $position->z >= $this->getMinZ() and $position->z <= $this->getMaxZ() and $position->y >= $this->getMinY() and $position->y <= $this->getMaxY()) {
            return true;
        }
        return false;
    }

}
