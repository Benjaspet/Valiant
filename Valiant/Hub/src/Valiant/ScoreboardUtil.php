<?php

declare(strict_types=1);

namespace Valiant;

use pocketmine\network\mcpe\protocol\RemoveObjectivePacket;
use pocketmine\network\mcpe\protocol\SetDisplayObjectivePacket;
use pocketmine\network\mcpe\protocol\SetScorePacket;
use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;
use pocketmine\Player;
use Valiant\Main;
use Valiant\Query\QueryUtil;

class ScoreboardUtil {

    private $plugin;

    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
    }

    public function setHubScoreboard(Player $player, string $title = "Valiant") : void {
        $totalcount = count($this->plugin->getServer()->getOnlinePlayers()) + QueryUtil::getNaPlayerCount();
        $this->lineTitle($player, " §c§l" . $title . " ");
        $this->lineCreate($player, 0, str_repeat(" ", 20));
        $this->lineCreate($player, 1, "§cHub: §f" . count($this->plugin->getServer()->getOnlinePlayers()));
        $this->lineCreate($player, 2, str_repeat(" ", 4));
        $this->lineCreate($player, 3, "§cPing: §f" . $player->getPing() . "ms");
        $this->lineCreate($player, 4, str_repeat(" ", 3));
        $this->lineCreate($player, 5, "§cvaliantpvp.tk");
    }

    public function updatePingLine(Player $player) {
        $this->lineRemove($player, 3);
        $this->lineCreate($player, 3, "§cPing: §f" . $player->getPing() . "ms");
    }

    public function lineTitle(Player $player, string $title){
        $packet = new SetDisplayObjectivePacket();
        $packet->displaySlot = "sidebar";
        $packet->objectiveName = "objective";
        $packet->displayName = $title;
        $packet->criteriaName = "dummy";
        $packet->sortOrder = 0;
        $player->sendDataPacket($packet);
    }

    public function removeScoreboard(Player $player){
        $packet = new RemoveObjectivePacket();
        $packet->objectiveName = "objective";
        $player->sendDataPacket($packet);
    }
    public function lineCreate(Player $player, int $line, string $content){
        $packetline = new ScorePacketEntry();
        $packetline->objectiveName = "objective";
        $packetline->type = ScorePacketEntry::TYPE_FAKE_PLAYER;
        $packetline->customName = " ". $content . "   ";
        $packetline->score = $line;
        $packetline->scoreboardId = $line;
        $packet = new SetScorePacket();
        $packet->type = SetScorePacket::TYPE_CHANGE;
        $packet->entries[] = $packetline;
        $player->sendDataPacket($packet);
    }

    public function lineRemove(Player $player, int $line){
        $entry = new ScorePacketEntry();
        $entry->objectiveName="objective";
        $entry->score = $line;
        $entry->scoreboardId = $line;
        $packet = new SetScorePacket();
        $packet->type = SetScorePacket::TYPE_REMOVE;
        $packet->entries[] = $entry;
        $player->sendDataPacket($packet);
    }
}