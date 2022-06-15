<?php

declare(strict_types=1);

namespace Valiant;

use pocketmine\plugin\PluginBase;
use Valiant\Command\PingCMD;
use Valiant\Query\QueryUtil;
use Valiant\Query\QueryTask;
use Valiant\ScoreboardUtil;

class Main extends PluginBase {

    public static $instance;
    private $queryutil;
    private $scoreboardutil;

    public function onEnable() {
        self::$instance = $this;
        $this->setListeners();
        $this->initUtil();
        $this->setCommands();
        $this->scoreboardutil = new ScoreboardUtil($this);
        $this->getScheduler()->scheduleRepeatingTask(new QueryTask($this), 200);
    }

    public function setListeners() {
        $map = $this->getServer()->getPluginManager();
        $map->registerEvents(new Listener($this), $this);
    }

    public function initUtil() {
        $this->queryutil = new QueryUtil($this);
    }

    public function setCommands() {
        $map = $this->getServer()->getCommandMap();
        $map->register("ping", new PingCMD($this));
    }

    public function getScoreboardUtil(): ScoreboardUtil {
        return $this->scoreboardutil;
    }

    public function getQueryUtil(): QueryUtil {
        return $this->queryutil;
    }
}