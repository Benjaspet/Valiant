<?php

declare(strict_types=1);

namespace Valiant\Utils;

use pocketmine\command\CommandSender;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\level\Level;
use pocketmine\level\particle\ExplodeParticle;
use pocketmine\level\particle\HugeExplodeParticle;
use pocketmine\level\Position;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\Player;
use pocketmine\utils\Config;
use Valiant\Core;
use Valiant\Libs\CustomForm;
use Valiant\Task\Async\ProxyTask;

class VUtils {

    const LOBBY = "Lobby";

    private $plugin;
    public $clicks;
    public $acceptProtocol;
    protected $chatcooldown = false;
    protected $cpsflags = 0;
    protected $reachflags = 0;
    protected $email = "spamnow31@yahoo.com";
    protected $disguised = false;

    public function __construct(Core $plugin) {
        $this->plugin = $plugin;
    }

    public function teleportToHub(Player $player)
    {
        $lobby = $this->plugin->getServer()->getLevelByName("Lobby-2");
        $pos = new Position(2001, 44.27, 2036, $lobby);
        $player->teleport($pos);
    }

    public function setTagged($player, bool $value = true, int $time = 15) {
        if ($player instanceof Player) $player = $player->getName();
        if ($value) {
            $this->plugin->getPlayerListener()->combat[$player] = $time;
            return;
        }
        unset($this->plugin->getPlayerListener()->combat[$player]);
    }

    public function isTagged($player): bool {
        if ($player instanceof Player) $player = $player->getName();
        return isset($this->plugin->getPlayerListener()->combat[$player]);
    }

    public function getTagDuration($player): int {
        if ($player instanceof Player) $player = $player->getName();
        return $this->isTagged($player) ? $this->plugin->getPlayerListener()->combat[$player] : 0;
    }

    public function getLobby(): string {
        return self::LOBBY;
    }

    public function isClientIdBanned($cid): bool {
        return isset ($this->plugin->clientbans[$cid]);
    }

    public function isPlayerBanned(Player $player): bool {
        return $this->isClientIdBanned($player->getClientId());
    }

    public function getFFA(Player $player): string {
        return $player->getLevel()->getFolderName();
    }

    public function teleportSound(Player $player) {
        if (is_null($player)) return;
        $sound = new PlaySoundPacket();
        $sound->soundName = "mob.endermen.portal";
        $sound->x = $player->getX();
        $sound->y = $player->getY();
        $sound->z = $player->getZ();
        $sound->volume = 10;
        $sound->pitch = 1;
        foreach ($player->getLevel()->getPlayers() as $players) {
            $players->dataPacket($sound);
        }
    }

    public function harpSound(Player $player, $pitch) {
        if (is_null($player)) return;
        $sound = new PlaySoundPacket();
        $sound->soundName = "note.harp";
        $sound->x = $player->getX();
        $sound->y = $player->getY();
        $sound->z = $player->getZ();
        $sound->volume = 10;
        $sound->pitch = $pitch;
        foreach ($player->getLevel()->getPlayers() as $players) {
            $players->dataPacket($sound);
        }
    }

    public function triHitSound($player)
    {
        if (is_null($player)) return;
        $sound = new PlaySoundPacket();
        $sound->soundName = "item.trident.hit";
        $sound->x = $player->getX();
        $sound->y = $player->getY();
        $sound->z = $player->getZ();
        $sound->volume = 0.7;
        $sound->pitch = 1;
        foreach ($player->getLevel()->getPlayers() as $players) {
            $players->dataPacket($sound);
        }
    }

    public function setChatCooldown(Player $player, bool $value): bool
    {
        return $this->chatcooldown = $value;
    }

    public function isInChatCooldown(): bool
    {
        return $this->chatcooldown !== false;
    }

    public function setCpsFlags(Player $player, int $int)
    {
        $this->cpsflags = $int;
    }

    public function addCpsFlag(Player $player)
    {
        $this->cpsflags = $this->cpsflags + 1;
    }

    public function getCpsFlags(Player $player): int
    {
        return $this->cpsflags;
    }

    public function addReachFlag(Player $player)
    {
        $this->reachflags = $this->reachflags + 1;
    }

    public function setReachFlags(Player $player, int $int)
    {
        $this->reachflags = $int;
    }

    public function getReachFlags(Player $player): int
    {
        return $this->reachflags;
    }

    public function removeBanExpired()
    {
        $this->plugin->getServer()->getNameBans()->removeExpired();
        $this->plugin->getServer()->getIPBans()->removeExpired();
    }

    public function hashIp(string $address): string
    {
        $ip = $address;
        return substr(md5($ip), 0, 15);
    }

    public static function getChatFormat($rank): string {
        switch ($rank) {
            case "Player":
                return "§7{clan}§r§f{name}§7: {message}";
                break;
            case "Voter":
                return "§7{clan}§r§f[§6Voter§f] {name}: §6{message}";
                break;
            case "Elite":
                return "§7{clan}§r§f[§aElite§f] {name}§f: §a{message}";
                break;
            case "Premium":
                return "§7{clan}§r§f[§bPremium§f] {name}§f: §b{message}";
                break;
            case "Booster":
                return "§7{clan}§r§f[§5Booster§f] {name}§f: §5{message}";
                break;
            case "YouTube":
                return "§7{clan}§r§f[§cYou§fTube] {name}§f: §c{message}";
                break;
            case "Famous":
                return "§7{clan}§r§f[§d§oFamous§r§f] {name}§f: §d{message}";
                break;
            case "Trainee":
                return "§7{clan}§r§f§a[Trainee] {name}§f: {message}";
                break;
            case "Helper":
                return "§7{clan}§r§f§5[Helper] {name}§f: {message}";
                break;
            case "Mod":
                return "§7{clan}§r§f§9[Mod] {name}§f: {message}";
                break;
            case "HeadMod":
                return "§7{clan}§r§f§3[Head-Mod] {name}§f: {message}";
                break;
            case "Admin":
                return "§7{clan}§r§f§6[Admin] {name}§f: {message}";
                break;
            case "Manager":
                return "§7{clan}§r§f§c[Manager] {name}§f: {message}";
                break;
            case "Owner":
                return "§7{clan}§r§f§4[Owner] {name}§f: {message}";
                break;
            default:
                return "§7{clan}§r§f§7{name}§f: {message}";
                break;
        }
    }

    public function cosmeticsForm(Player $player): void {
        $form = new CustomForm(function (Player $player, $data = null): void {
            $color = $data[0];
            switch ($data) {
                case 0:
                    return;
                    break;
            }
            switch ($data[0]) {
                case 0:
                    $color = "default";
                    if ($player->isOp() or $player->hasPermission("valiant.premium")) {
                        $player->sendMessage("§cYour potion splash color is set to red.");
                    } else {
                        $player->sendMessage("§cYou do not have access to this.");
                    }
                    break;
                case 1:
                    $color = "pink";
                    if ($player->isOp() or $player->hasPermission("valiant.premium")) {
                        $player->sendMessage("§cYour potion splash color is set to pink.");
                    } else {
                        $player->sendMessage("§cYou do not have access to this.");
                    }
                    break;
                case 2:
                    $color = "cyan";
                    if ($player->isOp() or $player->hasPermission("valiant.premium")) {
                        $player->sendMessage("§cYour potion splash color is set to cyan.");
                    } else {
                        $player->sendMessage("§cYou do not have access to this.");
                    }
                    break;
                case 3:
                    $color = "green";
                    if ($player->isOp() or $player->hasPermission("valiant.premium")) {
                        $player->sendMessage("§cYour potion splash color is set to green.");
                    } else {
                        $player->sendMessage("§cYou do not have access to this.");
                    }
                    break;
                case 4:
                    $color = "yellow";
                    if ($player->isOp() or $player->hasPermission("valiant.premium")) {
                        $player->sendMessage("§cYour potion splash color is set to yellow.");
                    } else {
                        $player->sendMessage("§cYou do not have access to this.");
                    }
                    break;
                case 5:
                    $color = "orange";
                    if ($player->isOp() or $player->hasPermission("valiant.premium")) {
                        $player->sendMessage("§cYour potion splash color is set to orange.");
                    } else {
                        $player->sendMessage("§cYou do not have access to this.");
                    }
                    break;

            }
            switch($data[1]) {
                case 0:
                    $this->plugin->getScoreboardUtil()->removeScoreboard($player);
                    $player->sendMessage("§aSettings updated.");
                    break;
                case 1:
                    $this->plugin->getScoreboardUtil()->setHubScoreboard($player);
                    $player->sendMessage("§aSettings updated.");
                    break;
            }
        });
        $colors = ["§cRed", "§dPink", "§bAqua", "§aGreen", "§eYellow", "§6Orange"];
        $form->setTitle("§8§lPLAYER SETTINGS");
        $def1 = -1;
        $form->addStepSlider("Potion Color", $colors, $def1, null);
        $form->addToggle("Scoreboard [on/off]", true);
        $player->sendForm($form);
    }

    public function spawnParticle(Player $player, $particle){
        switch($particle) {
            case "smoke":
                $players=[$player];
                $player->getlevel()->addParticle(new ExplodeParticle($player->asVector3()), $players);
                $player->getlevel()->addParticle(new ExplodeParticle($player->asVector3()->add(1, 0, 0)), $players);
                $player->getlevel()->addParticle(new ExplodeParticle($player->asVector3()->add(-1, 0, 0)), $players);
                $player->getlevel()->addParticle(new ExplodeParticle($player->asVector3()->add(0, 1, 0)), $players);
                $player->getlevel()->addParticle(new ExplodeParticle($player->asVector3()->add(0 , 0, 1)), $players);
                $player->getlevel()->addParticle(new ExplodeParticle($player->asVector3()->add(0 , 0, -1)), $players);
                break;
        }
    }

    public function getCPS(Player $player): int {
        if(!isset($this->clicks[$player->getLowerCaseName()])){
            return 0;
        }
        $time = $this->clicks[$player->getLowerCaseName()][0];
        $clicks = $this->clicks[$player->getLowerCaseName()][1];
        if($time !== time()){
            unset($this->clicks[$player->getLowerCaseName()]);
            return 0;
        }
        return $clicks;
    }

    public function addCPS(Player $player) {
        if(!isset($this->clicks[$player->getLowerCaseName()])){
            $this->clicks[$player->getLowerCaseName()] = [time(), 0];
        }
        $time = $this->clicks[$player->getLowerCaseName()][0];
        $clicks = $this->clicks[$player->getLowerCaseName()][1];
        if($time !== time()){
            $time = time();
            $clicks = 0;
        }
        $clicks++;
        $this->clicks[$player->getLowerCaseName()] = [$time, $clicks];
    }

    public function knockbackPlayer(Player $player) {
        if (is_null($player)) return;
        $level = $player->getLevel();
        $x = $player->getX();
        $y = $player->getY();
        $z = $player->getZ();
        $direction = $player->getDirectionVector();
        $dx = $direction->getX();
        $dy = $direction->getY();
        $player->knockBack($player, 0, $dx, $dy);
    }

    public function knockBackVPlayer(Player $player): void {
        if ($player instanceof Player) {
            switch ($player->getLevel()->getFolderName()) {
                case "NoDebuff-FFA":
                    $xkb = 0.387;
                    $ykb = 0.389;
                    $player->knockBack($player, 0, $xkb, $ykb);
                    break;
                case "Resistance-FFA":
                    $xkb = 0.370;
                    $ykb = 0.398;
                    $player->knockBack($player, 0, $xkb, $ykb);
                    break;
                case "Combo-FFA":
                    $xkb = 0.210;
                    $ykb = 0.250;
                    $player->knockBack($player, 0, $xkb, $ykb);
                    break;
            }
        }
    }

    public function preparePlayer(Player $player) {
        if ($player instanceof Player) {
            $name = $player->getName();
            $ip = $player->getAddress();
            $file0 = new Config($this->plugin->getDataFolder() . "ipdb/" . $ip . ".txt", CONFIG::ENUM);
            $file0->set($name);
            $file0->save();
            $cid = $player->getClientId();
            $xuid = $player->getXuid();
            $ip = $player->getAddress();
            $file = new Config($this->plugin->getDataFolder() . "ipdb/" . $ip . ".txt");
            $names = $file->getAll(true);
            $aliases = implode(', ', $names);
            $arr = [$name, $ip, $cid, $xuid];
            $arr[] = $aliases;
            $file1 = new Config($this->plugin->getDataFolder() . "playerdata/" . $name . ".yml", CONFIG::YAML);
            $file1->set("Player Data", $arr);
            $file1->save();
            $this->plugin->getScoreboardUtil()->setHubScoreboard($player);
            $player->setGamemode(2);
            $player->setFood(20);
            $player->setXpProgress(0);
            $player->setHealth(20);
            $player->setXpLevel(0);
            $player->getInventory()->clearAll();
            $player->getArmorInventory()->clearAll();
            $player->removeAllEffects();
            $player->setFood(20);
            $player->sendMessage("§aWelcome to Valiant, " . $player->getName() . "!");
            $player->sendTitle("§l§cValiant", "§7Practice", 10, 30, 10);
            $this->plugin->getUtils()->triHitSound($player);
            $this->plugin->getUtils()->harpSound($player, 3);
            $this->plugin->getUtils()->teleportToHub($player);
            $this->plugin->getKitUtil()->sendKit($player, "Lobby");
            if(!is_dir($this->plugin->getDataFolder() . "playerdata/")){
                @mkdir($this->plugin->getDataFolder() . "playerdata/", 0777, true);
            }
            if(!is_dir($this->plugin->getDataFolder() . "ipdb/")){
                @mkdir($this->plugin->getDataFolder() . "ipdb/", 0777, true);
            }
        }
    }

    public function acceptProtocol(DataPacketReceiveEvent $ev) {
        $pk = $ev->getPacket();
        if ($pk instanceof LoginPacket) {
            if (in_array($pk->protocol, $this->acceptProtocol)) {
                $pk->protocol = ProtocolInfo::CURRENT_PROTOCOL;
            }
        }
    }

    public function isDisguised(Player $player): bool {
        return $this->disguised!==false;
    }

    public function setDisguised(Player $player, bool $value){
        $this->disguised = $value;
    }

    public function getFakeNames(): array {
        return ["Trapzies","ghxsty","LuckyXTapz","obeseGamerGirl","UnknownXzzz","zAnthonyyy","FannityPE",
            "Vatitelc","StudSport","MCCaffier","Keepuphulk8181","LittleComfy","Decdarle","mythic_d4nger",
            "gambling life","BASIC x VIBES","lawlogic","hutteric","BiggerCobra_1181","Lextech817717",
            "Chnixxor","AloneShun","AddictedToYou","Board","Javail","MusicPqt","REYESOOKIE","Asaurus Rex",
            "Popperrr","oopsimSorry_","lessthan greaterthan","Regrexxx","adam 22","NotCqnadian","brtineyMCPE",
            "samanthaplayzmc","ShaniquaLOL","OptimusPrimeXD","BouttaBust","GamingNut66","NoIdkbruh","ThisIsWhyYoure___",
            "voLT_811","Sekrum","Artificial_","ReadMyBook","urmum__77","idkwhatiatetoday","udkA77161","Stimpy","Adviser",
            "St1pmyPVP","GangGangGg","CoolKid888","AcornChaser78109","anon171717","AnonymousYT","Sintress Balline",
            "Daviecrusha","HeatedBot46","CobraKiller2828","KingPVPYT","TempestG","ThePVPGod","McProGangYT","lmaonocap",
            "NoClipXD","ImHqcking","undercoverbot","reswoownss199q","diego91881","CindyPlayz","HeyItzMe","iTzSkittlesMC",
            "NOHACKJUSTPRO","idkHowToPlay","Bum Bummm","Bigumslol","Skilumsszz","SuperGamer756","ProPVPer2k20",
            "N0S3_P1CK3R84","PhoenixXD","EnderProYT_81919","Ft MePro","NotHaqing","aababah_a","badbtch4life","serumxxx",
            "bigdogoo_","william18187","ZeroLxck","Gamer dan","SuperSAIN","DefNoHax","GoldFox","ClxpKxng","AdamIsPro",
            "XXXPRO655","proshtGGxD","GamerKid9000","SphericalAxeum","ImABot"];
    }
}