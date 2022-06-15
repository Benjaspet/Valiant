<?php

declare(strict_types=1);

namespace Valiant;

use pocketmine\Player;

class PracticePlayer {

    public static $data;

    public static function getKills(Player $player) {
        return self::$data[$player->getName()]["kills"];
    }

    public static function setKills(Player $player, int $amount) {
        self::$data[$player->getName()]["kills"] = $amount;
    }

    // so how would you access this class without using base player class



}


