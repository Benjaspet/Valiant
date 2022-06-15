<?php

namespace Valiant\Query;

use Valiant\Libs\Query\NetworkQuery;
use Valiant\Main;

class QueryUtil {

    private $plugin;

    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
    }

    /*public static function getTotalPlayerCount(): string {
        $na = new NetworkQuery("45.134.8.14", 19132);
        $countna = $na->getPlayersCount();
        $hub = new NetworkQuery("192.99.248.212", 19132);
        $counthub = $hub->getPlayersCount();

        $num1 = (int)$countna;
        $num2 = (int)$counthub;

        $totalstring = $num1 + $num2;
        $totalcount = strval($totalstring);

        return $totalcount;
    }*/

    public static function getNaPlayerCount(): int {
        $na = new NetworkQuery("45.134.8.14", 19132);
        $countna = $na->getPlayersCount();
        $num1 = (int)$countna;
        return $num1;
    }

    /*public static function getHubPlayerCount(): int {
        $hub = new NetworkQuery("45.134.8.14", 19132);
        $counthub = $hub->getPlayersCount();
        $num1 = (int)$counthub;
        return $num1;
    }*/
}