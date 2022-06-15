<?php

namespace Valiant\Command;

use pocketmine\command\PluginCommand;
use pocketmine\Player;
use pocketmine\command\CommandSender;
use Valiant\Core;

class DuelCMD extends PluginCommand {

    private $plugin;

    public function __construct(Core $plugin) {
        parent::__construct("practice", $plugin);
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if(!$sender instanceof Player) {
            $sender->sendMessage("§cRun this command as a player.");
            return false;
        }
        if(!isset($args[0])) {
            $sender->sendMessage($this->getUsage());
            return false;
        }
        switch(array_shift($args)) {
            case "help":
                break;
            case "join":
                if(count($args) !== 1) {
                    $sender->sendMessage("§cUsage: §7/duel join {arena}");
                    return false;
                }
                if($arena = $this->plugin->getDuelHandler()->getArenaByName($args[0])) {
                    $this->getPlugin()->getServer()->loadLevel($arena->getWorldName());
                    $arenaname = $arena->getName();
                    $activeduels = $this->plugin->getDuelHandler()->getActiveDuels()->getAll();
                    if(!isset($activeduels[$arenaname]["opponent"])) {
                        $this->plugin->getDuelHandler()->getActiveDuels()->setNested("$arenaname.opponent", "none");
                        $this->plugin->getDuelHandler()->getActiveDuels()->setNested("$arenaname.player1", $sender->getName());
                        $this->plugin->getDuelHandler()->getActiveDuels()->save();
                        $arena->join($sender);
                        $this->plugin->getDuelHandler()->addQueueItem($sender);
                    } elseif($this->plugin->getDuelHandler()->getActiveDuels()->getNested("$arenaname.opponent") === "none") {
                        $this->plugin->getDuelHandler()->getActiveDuels()->setNested("$arenaname.player2", $sender->getName());
                        $this->plugin->getDuelHandler()->getActiveDuels()->save();
                        $arena->join($sender);
                    }
                }else{
                    $sender->sendMessage("§cThat arena does not exist.");
                }
                break;
            case "quit":
                if($arena = $this->plugin->getDuelHandler()->getArenaByPlayer($sender)) {
                    $arena->quit($sender);
                    $this->plugin->getDuelHandler()->removeQueueItem($sender);
                }else{
                    $sender->sendMessage("§cYou aren't in an arena.");
                }
                break;
            default:
                $sender->sendMessage("§cUsage: §7/duel {join, quit} {arena}");
                break;
        }
        return true;
    }
}