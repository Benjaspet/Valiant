<?php

declare(strict_types=1);

namespace Valiant\Command;

use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\level\Position;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\Player;
use pocketmine\command\PluginCommand;
use pocketmine\command\CommandSender;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\TextFormat;
use Valiant\Core;
use Valiant\Task\EventTask;
use Valiant\Task\StartTask;

class EventCMD extends PluginCommand {

    private $plugin;
    public $participants = [];
    public $fighting = [];
    private $sumoevent = false;
    private $started = false;
    public $roundinprogress = false;
    private $round = 0;

    const NO_EVENT = "§cThere is no event currently running.";

    public function __construct(Core $plugin){
        parent::__construct("event", $plugin);
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if (!isset($args[0])) {
            $sender->sendMessage("§cUsage: §7/event {create, start, join, leave, end}");
            return true;
        }
        switch (strtolower($args[0])) {
            case "create":
                if (!$sender->hasPermission("valiant.event.host")) {
                    $sender->sendMessage("§cYou do not have permission to create sumo events.");
                    return true;
                }
                if ($this->sumoevent) {
                    $sender->sendMessage("§cAn event is already running.");
                    return true;
                }
                $this->sumoevent = true;
                $sender->sendMessage("§aYou have started a sumo event.");
                $this->plugin->getServer()->broadcastMessage("§a" . $sender->getName() . " has started a sumo event! To join, run §l/event join§r§a.");
                break;
            case "start":
                if (!$sender->hasPermission("valiant.event.host")) {
                    $sender->sendMessage("§cYou do not have permission to create sumo events.");
                    return true;
                }
                if ($this->started) {
                    $sender->sendMessage("§cThe sumo event has already started.");
                    return true;
                }
                if (!$this->sumoevent) {
                    $sender->sendMessage(self::NO_EVENT);
                    return true;
                }
                if (count($this->participants) <= 1 || count($this->plugin->getServer()->getOnlinePlayers()) <= 1) {
                    $sender->sendMessage("§cThere are not enough players online to start the event.");
                    return true;
                }
                $this->started = true;
                $sender->sendMessage("§aThe sumo event has been started successfully.");
                $this->plugin->getScheduler()->scheduleRepeatingTask(new StartTask($this->plugin), 20);
                foreach ($this->participants as  $participant) {
                    $player = $this->plugin->getServer()->getPlayer($participant);
                }
                break;
            case "round":
                if (!$sender->hasPermission("valiant.event.host")) {
                    $sender->sendMessage("§cYou do not have permission to execute this command.");
                    return true;
                }
                if (!$this->sumoevent) {
                    $sender->sendMessage(self::NO_EVENT);
                    return true;
                }
                if (!$this->started) {
                    $sender->sendMessage("§cThe event has not started yet.");
                    return true;
                }
                if(count($this->participants) > 1){
                    list($red, $blue) = array_chunk($this->participants, (count($this->participants) / 2));
                } else {
                    $this->endSumoEvent();
                    return true;
                }
                if ($this->roundinprogress) {
                    $sender->sendMessage("§cThere is currently a round in progress.");
                    return true;
                }
                $this->roundinprogress = true;
                $player1 = $this->plugin->getServer()->getPlayer($red[array_rand($red)]);
                $player2 = $this->plugin->getServer()->getPlayer($blue[array_rand($blue)]);
                $this->round++;
                $p1 = $player1->getName(); $p2 = $player2->getName();
                $this->fighting[] = $p1;
                $this->fighting[] = $p2;
                $player1->setImmobile(true); $player2->setImmobile(true);
                $rn = $this->round;
                $worldd = $this->plugin->getServer()->getLevelByName("Sumo-Event");
                $player1->teleport($worldd->getSafeSpawn());
                $player2->teleport($worldd->getSafeSpawn());

                $pos = [9994.53, 93, 10003.52];
                $pos2 = [10002.4,93,10003.46];

                $player1->teleport(new Position($pos[0], $pos[1],$pos[2],$worldd));
                $player2->teleport(new Position($pos2[0],$pos2[1],$pos2[2],$worldd));
                $player1->sendMessage("§aOpponent: §l" . $p2);
                $player2->sendMessage("§aOpponent: §l" . $p1);
                foreach ($this->plugin->getServer()->getOnlinePlayers() as $eventparticipant) {
                    if ($eventparticipant->getLevel()->getFolderName() === $worldd) {
                        $eventparticipant->sendPopup("§a" . $p1 . " vs. " . $p2);
                    }
                }
                $this->plugin->getScheduler()->scheduleDelayedTask(new ClosureTask(
                    function (int $currentTick) use ($player1, $player2): void {
                        if(!in_array($player1->getName(),$this->fighting)){
                            $this->endRound($player1, $player2);
                            $player2->setImmobile(false);
                            $this->removeFighting($player2->getName());
                            return;
                        }
                        if(!in_array($player2->getName(), $this->fighting)) {
                            $this->endRound($player2, $player1);
                            $player1->setImmobile(false);
                            $this->removeFighting($player1->getName());
                            return;
                        }
                        foreach([$player1, $player2] as $players) {
                            $players->setImmobile(false);
                            $players->sendTitle("§l§aFight!", "", 5, 15, 5);
                            $this->sumoEffect($players);
                        }
                    }
                ), 100);
                break;
            case "spectate":
                if(!$this->sumoevent) {
                    $sender->sendMessage(self::NO_EVENT);
                    return true;
                }
                if(!$this->started){
                    $sender->sendMessage(TextFormat::RED . "The sumo event has not been started.");
                    return true;
                }
                $world = $this->plugin->getServer()->getLevelByName("Sumo-Event");
                if ($sender instanceof Player) {
                    if($sender->getLevel()->getName() === $world){
                        $sender->sendMessage(TextFormat::RED . "You are already in the sumo event world.");
                        return true;
                    }
                }
                $sumomap = $this->plugin->getServer()->getLevelByName("Sumo-Event");
                $pos = new Position(9984.55, 93, 10003.49, $sumomap);
                $sender->teleport($pos);
                break;
            case "join":
                if (!$this->sumoevent) {
                    $sender->sendMessage(self::NO_EVENT);
                    return true;
                }
                if ($this->started) {
                    $sender->sendMessage("§cThe event has already started.");
                    return true;
                }
                if (!in_array($sender->getName(), $this->participants)){
                    $this->participants[] = $sender->getName();
                    $sender->sendMessage("§aYou have joined the sumo event!");
                    if ($sender instanceof Player) {
                        $sender->removeAllEffects();
                        $sender->getInventory()->clearAll();
                        $sender->getArmorInventory()->clearAll();
                        $sender->setHealth(20);
                    }
                    $this->plugin->getServer()->broadcastMessage("§a" . $sender->getName() . " has joined the sumo event. §7[" . count($this->participants) . "]");
                    $sumomap = $this->plugin->getServer()->getLevelByName("Sumo-Event");
                    $pos = new Position(9984.55, 93, 10003.49, $sumomap);
                    $sender->teleport($pos);
                } else {
                    $sender->sendMessage("§cYou are already in the event.");
                }
                break;
            case "leave":
                if (!$this->sumoevent){
                    $sender->sendMessage(TextFormat::RED . self::NO_EVENT);
                    return true;
                }
                if (in_array($sender->getName(), $this->participants)) {
                    if (!$this->started) {
                        $this->removePlayer($sender->getName());
                        $sender->sendMessage("§cYou left the sumo event.");
                        if ($sender instanceof Player) {
                            $this->plugin->getUtils()->teleportToHub($sender);
                        }
                    } else {
                        $sender->sendMessage("§cThe event has already started.");
                    }
                } else {
                    $sender->sendMessage("§cYou are not in a sumo event.");
                }
                break;
            case "end":
                if (!$sender->hasPermission("helios.high.staff")){
                    $sender->sendMessage("§cYou do not have permission to end an event.");
                    return true;
                }
                if (!$this->sumoevent){
                    $sender->sendMessage(TextFormat::RED . self::NO_EVENT);
                    return true;
                }
                $this->endSumoEvent();
                $sender->sendMessage("§cThe event was ended successfully.");
                if ($sender instanceof Player) {
                    $this->plugin->getUtils()->teleportToHub($sender);
                }
                break;
        }
        return true;
    }

    public function onLevelChange(EntityLevelChangeEvent $event) {
        $player = $event->getEntity();
        if ($player instanceof Player) {
            $name = $player->getName();
            if(!in_array($name, $this->fighting)) return;
            if(in_array($name, $this->participants)){
                $this->removePlayer($name);
                $this->roundinprogress = false;
                $this->plugin->getScheduler()->scheduleRepeatingTask(new EventTask($this->plugin), 20);
            }
            $this->removeFighting($name);
        }
    }

    public final function getRemainingSumoEventCount(): int {
        return count($this->participants);
    }

    public function onQuit(PlayerQuitEvent $event){
        if (!$this->sumoevent) return;
        $player = $event->getPlayer();
        $name = $player->getName();
        if (in_array($name, $this->participants)){
            $this->removePlayer($name);
        }
        if (in_array($name, $this->fighting)){
            $this->removeFighting($name);
        }
    }

    public function endSumoEvent() {
        if ($this->started){
            if (count($this->participants) <= 1){
                // $winner = $this->participants[array_key_first($this->participants)];
                $this->roundinprogress = false;
                $this->plugin->getServer()->broadcastPopup("§aThe sumo event has ended!");
            } else {
                $this->plugin->getServer()->broadcastMessage("§aThe sumo event has ended. A winner could not be determined.");
            }
            $world = $this->plugin->getServer()->getLevelByName("Sumo-Event");
            foreach ($this->plugin->getServer()->getLevelByName("Sumo-Event")->getEntities() as $players){
                if ($players instanceof Player){
                    $players->removeAllEffects();
                    $this->plugin->getUtils()->teleportToHub($players);
                }
            }
        }
        $this->sumoevent = false;
        $this->started = false;
        $this->participants = [];
        $this->round = 0;
    }

    public function endRound(Player $player, Player $player2){
        if(!$this->roundinprogress) return;
        $world = $this->plugin->getServer()->getLevelByName("Sumo-Event");
        if(in_array($player->getName(), $this->participants)){
            $winner = $player;
            $loser = $player2;
        } elseif(in_array($player2->getName(), $this->participants)){
            $winner = $player2;
            $loser = $player;
        }
        if($winner->getLevel()->getName() === $world){
            $winner->teleport($this->plugin->getServer()->getLevelByName($world)->getSafeSpawn());
            $winner->getInventory()->clearAll();
            $winner->getArmorInventory()->clearAll();
            $this->plugin->getServer()->broadcastMessage("§a" . $winner->getName() . " won the match vs. " . $loser->getName() . ".");
            if (count($this->participants) <= 1) {
                $this->endSumoEvent();
            }
            $this->roundinprogress = false;
        }
    }

    public function removePlayer(string $string){
        if (($key = array_search($string, $this->participants)) !== false) {
            unset($this->participants[$key]);
        }
    }

    public function removeFighting(string $string){
        if(($key = array_search($string, $this->fighting)) !== false) {
            unset($this->fighting[$key]);
        }
    }

    public function sumoEffect(Player $player) {
        $player->addEffect(new EffectInstance(Effect::getEffect(Effect::RESISTANCE), 1500000, 10, false));
    }

    public function getEventUtil(): EventCMD {
        return $this;
    }
}