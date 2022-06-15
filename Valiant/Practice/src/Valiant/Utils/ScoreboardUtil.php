<?php

declare(strict_types=1);

namespace Valiant\Utils;

use pocketmine\network\mcpe\protocol\RemoveObjectivePacket;
use pocketmine\network\mcpe\protocol\SetDisplayObjectivePacket;
use pocketmine\network\mcpe\protocol\SetScorePacket;
use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;
use pocketmine\Player;
use Valiant\Core;

class ScoreboardUtil {

    private $plugin;

    public function __construct(Core $plugin) {
        $this->plugin = $plugin;
    }

    public function setHubScoreboard(Player $player, string $title = "Valiant") : void {
        $this->lineTitle($player, " §c§l" . $title . " ");
        $this->lineCreate($player, 0, str_repeat(" ", 20));
        $this->lineCreate($player, 1, "§cOnline: §f" . count($this->plugin->getServer()->getOnlinePlayers()));
        $this->lineCreate($player, 2, str_repeat(" ", 5));
        $this->lineCreate($player, 3, "§cPing: §f" . $player->getPing() . "ms");
        $this->lineCreate($player, 4, "§cCombat: §f0");
        $this->lineCreate($player, 5, str_repeat(" ", 4));
        $this->lineCreate($player, 6, "§cPearl: §f0");
        $this->lineCreate($player, 7, str_repeat(" ", 3));
        $this->lineCreate($player, 8, "§cvaliantpvp.tk");
    }

    public function setFFAScoreboard(Player $player, string $title = "Valiant") : void {
        $this->lineTitle($player, " §c§l" . $title . " ");
        $this->lineCreate($player, 0, str_repeat(" ", 20));
        $this->lineCreate($player, 1, "§cOnline: §f" . count($this->plugin->getServer()->getOnlinePlayers()));
        $this->lineCreate($player, 2, str_repeat(" ", 5));
        $this->lineCreate($player, 3, "§cPing: §f" . $player->getPing() . "ms");
        $this->lineCreate($player, 4, "§cCombat: §f0");
        $this->lineCreate($player, 5, str_repeat(" ", 4));
        $this->lineCreate($player, 6, "§cPearl: §f0");
        $this->lineCreate($player, 7, str_repeat(" ", 3));
        $this->lineCreate($player, 8, "§cvaliantpvp.tk");
    }

    public function setDuelScoreboard(Player $player1, Player $player2, string $id, string $title = "Valiant"): void {
        $duel = $this->plugin->getDuelManager()->getDuel($id);
        $this->lineTitle($player1, " §c§l" . $title . " ");
        $this->lineCreate($player1, 0, str_repeat(" ", 20));
        if ($duel->getRanked() === true) {
            $this->lineCreate($player1, 1, "§cLadder: §fRanked");
        } else {
            $this->lineCreate($player1, 1, "§cLadder: §fUnranked");
        }
        $this->lineCreate($player1, 2, "§cMode: §f" . $duel->getType());
        $this->lineCreate($player1, 3, "§cTime: §f" . $duel->getTime());
        $this->lineCreate($player1, 4, str_repeat(" ", 5));
        $this->lineCreate($player1, 5, "§cYour ping: §f" . $player1->getPing() . "ms");
        $this->lineCreate($player1, 6, "§cTheir ping: §f" . $player2->getPing());
        $this->lineCreate($player1, 7, str_repeat(" ", 4));
        $this->lineCreate($player1, 8, "§cvaliantpvp.tk");
    }

    public function updateMainLineOnlinePlayers(Player $player) {
        $this->lineRemove($player, 1);
        $this->lineCreate($player, 1, "§cOnline: §f" . count($this->plugin->getServer()->getOnlinePlayers()));
    }

    public function updatePingLine(Player $player) {
        $this->lineRemove($player, 3);
        $this->lineCreate($player, 3, "§cPing: §f" . $player->getPing() . "ms");
    }

    public function updateMainLineCombat($player, $timer){
        $this->lineRemove($player, 4);
        $this->lineCreate($player, 4, "§cCombat: §f" . $timer);
    }

    public function updatePearlLine(Player $player, int $timer) {
        $this->lineRemove($player, 6);
        $this->lineCreate($player, 6, "§cPearl: §f" . $timer);
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
