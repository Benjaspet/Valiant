<?php

namespace Valiant\Task\Async;

use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use Valiant\Core;

class ProxyTask extends AsyncTask {

    private $name;
    private $ip;

    public function __construct(string $name, string $ip) {
        $this->name = $name;
        $this->ip = $ip;
    }

    public function onRun() {
        $url = "http://check.getipintel.net/check.php?ip=" . $this->ip . "&format=json&contact=test@outlook.de";
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true
        ]);
        $result = curl_exec($curl);
        $data = json_decode($result, true);

        $this->setResult(array(
            "name" => (string) $this->name,
            "result" => $data["result"]
        ));
    }

    public function onCompletion(Server $server) {
        $result = (float) $this->getResult()["result"];
        $name = $this->getResult()["name"];

        if ($result !== null) {
            if ($result > 0.98) {
                $player = $server->getPlayerExact($name);
                $player->kick("§cYou were unable to connect to Valiant.\n§cError code: §7disguise\n§cContact us: §7" . Core::DISCORD, false);
            }
        }
    }
}