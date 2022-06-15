<?php

declare(strict_types=1);

namespace Valiant;

use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\Player;
use pocketmine\utils\Config;
use pocketmine\plugin\PluginBase;

use Sumo\Sumo;
use Valiant\Duels\DuelAPI;
use Valiant\Duels\DuelHandler;
use Valiant\Duels\DuelManager;
use Valiant\Event\Match\Map\MapManager;
use Valiant\Event\Session\SessionManager;
use Valiant\Event\SumoListener;
use Valiant\Generator\Generator;
use Valiant\Libs\CustomForm;
use Valiant\Libs\SimpleForm;
use Valiant\Task\CombatTask;
use Valiant\Task\PingTask;
use Valiant\Listeners\ACListener;
use Valiant\Listeners\PlayerListener;
use Valiant\Listeners\ServerListener;
use Valiant\Listeners\WorldListener;
use Valiant\Utils\ClickUtil;
use Valiant\Utils\DatabaseUtil;
use Valiant\Utils\FormUtil;
use Valiant\Utils\KitUtil;
use Valiant\Utils\Player\PlayerUtil;
use Valiant\Utils\ScoreboardUtil;
use Valiant\Utils\StaffUtil;
use Valiant\Utils\StringUtil;
use Valiant\Utils\VUtils;
use Valiant\Command\AliasCMD;
use Valiant\Command\FlyCMD;
use Valiant\Command\FreezeCMD;
use Valiant\Command\GamemodeCMD;
use Valiant\Command\HubCMD;
use Valiant\Command\KickCMD;
use Valiant\Command\NickCMD;
use Valiant\Command\OnlineCMD;
use Valiant\Command\PDataCMD;
use Valiant\Command\PingCMD;
use Valiant\Command\Punishments\MuteCMD;
use Valiant\Command\Punishments\PBanCMD;
use Valiant\Command\Punishments\UnbanCMD;
use Valiant\Command\Punishments\UnmuteCMD;
use Valiant\Command\RekitCMD;
use Valiant\Command\RestartCMD;
use Valiant\Command\StaffCMD;
use Valiant\Command\TpAllCMD;
use Valiant\Command\ReportCMD;
use Valiant\Command\EventCMD;
use Valiant\Command\ArenaCMD;
use Valiant\Command\DuelCMD;
use Valiant\Command\AnnounceCMD;
use Valiant\Command\DisguiseCMD;
use Valiant\Command\Punishments\SoftbanCMD;

class Core extends PluginBase {

    private static $instance;

    public $main;
    public $scoreboardutil;
    public $vutils;
    public $staffutils;
    public $kitutil;
    public $playerlistener;
    public $config;
    private $formutil;
    public $stringutil;
    public $clickutil;
    private $worldlistener;
    private $aclistener;
    private $duelmanager;
    private $dbutil;
    private $eventutil;
    private $playerutil;
    private $duelapi;
    private $generator;
    public $targetPlayer = [];
    public $acceptProtocol = [];
    public $permbans = [];

    private $mapManager;
    private $matchManager;
    private $sessionManager;

    public $db;

    const PREFIX = "§8[§aValiant§7]";
    const IP = "45.134.8.14";
    const DISCORD = "https://discord.gg/PBjrx4S9Wa";

    const BANLOGWEBHOOK = $this->getConfig()->get("banlogwebhook");
    const REPORTWEBHOOK = "";
    const KICKWEBHOOK = "https://discord.com/api/webhooks/815450933936783401/b_8ieHn0_b7vlJPt8grLanEbtk4cW23oWAuu7a1-VzNKK4c_G8d39ov_bzFjDuSuIb7M";
    const STATUSWEBHOOK = "https://discord.com/api/webhooks/812470805615083530/-c56cMYjJyYtlL9N-MVR_2T1_dyrjF4OKExJqTnVptwTZO0_QT9jGSzynKb5c39i5zET";

    public function onEnable() {

        self::$instance = $this;
        $this->reloadConfig();
        $this->initBanConfig();
        $this->saveData();
        $this->setTasks();
        $this->setListeners();
        $this->disableCommands();
        $this->registerCommands();
        $this->initInterface();
        $this->initResources();
        @mkdir($this->getDataFolder());

        $this->saveResource("config.yml");
        $this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        $this->acceptProtocol = (new Config($this->getDataFolder()."protocols.yml", Config::YAML))->get("accept-protocol");
        if ($this->acceptProtocol === false || empty($this->acceptProtocol)) {
            $this->acceptProtocol[] = ProtocolInfo::CURRENT_PROTOCOL;
            $config = new Config($this->getDataFolder() . "protocols.yml", Config::YAML);
            $config->set("accept-protocol", [ProtocolInfo::CURRENT_PROTOCOL]);
            $config->save();
        }
        foreach (array_diff(scandir($this->getServer()->getDataPath() . "worlds"), ["..", "."]) as $levelName){
            if ($this->getServer()->loadLevel($levelName)){
                $this->getLogger()->info("Loaded level ${levelName}.");
            }
        }

        $this->getLogger()->info("
         ------------------------

		 <-- Valiant enabled. -->

	     ------------------------
		");
    }

    public function setListeners() {
        $map = $this->getServer()->getPluginManager();
        $map->registerEvents(new PlayerListener($this), $this);
        $map->registerEvents(new ServerListener($this), $this);
        $map->registerEvents(new ACListener($this), $this);
        $map->registerEvents(new SumoListener($this), $this);
    }

    public function setTasks() {
        $map = $this->getScheduler();
        $map->scheduleRepeatingTask(new PingTask($this), 100);
        $map->scheduleRepeatingTask(new CombatTask($this), 20);
    }

    public function initInterface() {
        $this->scoreboardutil = new ScoreboardUtil($this);
        $this->vutils = new VUtils($this);
        $this->staffutils= new StaffUtil($this);
        $this->kitutil = new KitUtil($this);
        $this->formutil = new FormUtil($this);
        $this->clickutil = new ClickUtil($this);
        $this->dbutil = new DatabaseUtil($this);
        $this->playerutil = new PlayerUtil($this);
        $this->playerlistener = new PlayerListener($this);
        $this->worldlistener = new WorldListener($this);
        $this->aclistener = new ACListener($this);
        $this->eventutil = new EventCMD($this);
        $this->mapManager = new MapManager($this);
        $this->matchManager = new MatchManager($this);
        $this->sessionManager = new SessionManager($this);
    }

    public function registerCommands() {
        $map = $this->getServer()->getCommandMap();
        $map->register("hub", new HubCMD($this));
        $map->register("staff", new StaffCMD($this));
        $map->register("gamemode", new GamemodeCMD($this));
        $map->register("freeze", new FreezeCMD($this));
        $map->register("kick", new KickCMD($this));
        $map->register("tpall", new TpAllCMD($this));
        $map->register("ping", new PingCMD($this));
        $map->register("nick", new NickCMD($this));
        $map->register("online", new OnlineCMD($this));
        $map->register("fly", new FlyCMD($this));
        $map->register("alias", new AliasCMD($this));
        $map->register("restart", new RestartCMD($this));
        $map->register("mute", new MuteCMD($this));
        $map->register("unmute", new UnmuteCMD($this));
        $map->register("pinfo", new PDataCMD($this));
        $map->register("pban", new PBanCMD($this));
        $map->register("unban", new UnbanCMD($this));
        $map->register("rekit", new RekitCMD($this));
        $map->register("report", new ReportCMD($this));
        $map->register("arena", new ArenaCMD($this));
        $map->register("duel", new DuelCMD($this));
        $map->register("softban", new SoftbanCMD($this));
        $map->register("announce", new AnnounceCMD($this));
        $map->register("disguise", new DisguiseCMD($this));
        $map->register("sumo", new SumoCMD($this));
    }

    public function disableCommands() {
        $map = $this->getServer()->getCommandMap();
        $map->unregister($map->getCommand("me"));
        $map->unregister($map->getCommand("w"));
        $map->unregister($map->getCommand("version"));
        $map->unregister($map->getCommand("pl"));
        $map->unregister($map->getCommand("list"));
        $map->unregister($map->getCommand("kill"));
        $map->unregister($map->getCommand("enchant"));
        $map->unregister($map->getCommand("effect"));
        $map->unregister($map->getCommand("defaultgamemode"));
        $map->unregister($map->getCommand("spawnpoint"));
        $map->unregister($map->getCommand("setworldspawn"));
        $map->unregister($map->getCommand("title"));
        $map->unregister($map->getCommand("seed"));
        $map->unregister($map->getCommand("help"));
        $map->unregister($map->getCommand("particle"));
        $map->unregister($map->getCommand("gamemode"));
        $map->unregister($map->getCommand("fperms"));
        $map->unregister($map->getCommand("usrinfo"));
        $map->unregister($map->getCommand("kick"));
        $map->unregister($map->getCommand("ban"));
        $map->unregister($map->getCommand("banlist"));
        $map->unregister($map->getCommand("ban-ip"));
    }

    public function initBanConfig() {
        $this->reloadConfig();
        if(file_exists($this->getDataFolder() . 'bans.txt')){
            $file = file($this->getDataFolder() . 'bans.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach($file as $line){
                $array = explode('|', trim($line));
                $this->permbans[$array[0]] = $array[1];
            }
        }
    }

    public function initResources() {
        foreach(['config.yml'] as $file){
            $this->saveResource($file);
        }
    }

    public function saveData(){
        $string = '';
        foreach($this->permbans as $client => $name){
            $string .= $client.'|'.$name."\n";
        }
        file_put_contents($this->getDataFolder() . 'bans.txt', $string);
    }

    public function getScoreboardUtil(): ScoreboardUtil {
        return $this->scoreboardutil;
    }

    public function getUtils(): VUtils {
        return $this->vutils;
    }

    public function getKitUtil(): KitUtil {
        return $this->kitutil;
    }

    public function getFormUtil(): FormUtil {
        return $this->formutil;
    }

    public function getPlayerListener(): PlayerListener {
        return $this->playerlistener;
    }

    public function getStaffUtils(): StaffUtil {
        return $this->staffutils;
    }

    public function getStringUtil(): StringUtil {
        return $this->stringutil;
    }

    public function getClickUtil(): ClickUtil {
        return $this->clickutil;
    }

    public function getWorldListener(): WorldListener {
        return $this->worldlistener;
    }

    public function getDuelManager(): DuelManager {
        return $this->duelmanager;
    }

    public function getGenerator(): Generator {
        return $this->generator;
    }

    public function getDuelAPI(): DuelAPI {
        return $this->duelapi;
    }

    public function getDatabaseUtil(): DatabaseUtil {
        return $this->dbutil;
    }

    public function getEventUtil(): EventCMD {
        return $this->eventutil;
    }

    public function getPlayerUtil(): PlayerUtil {
        return $this->playerutil;
    }

    public static function getInstance(): Sumo {
        return self::$instance;
    }

    public function getMapManager(): MapManager {
        return $this->mapManager;
    }

    public function getMatchManager(): MatchManager {
        return $this->matchManager;
    }

    public function getSessionManager(): SessionManager {
        return $this->sessionManager;
    }
}