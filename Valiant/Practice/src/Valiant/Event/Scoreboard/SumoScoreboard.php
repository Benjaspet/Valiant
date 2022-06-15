<?php

declare(strict_types=1);

namespace Valiant\Event\Scoreboard;

use pocketmine\network\mcpe\protocol\RemoveObjectivePacket;
use pocketmine\network\mcpe\protocol\SetDisplayObjectivePacket;
use pocketmine\network\mcpe\protocol\SetScorePacket;
use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;
use pocketmine\Player;
use Valiant\Event\Session\Session;

class SumoScoreboard {

    private $plugin;

    private $session;

    public function __construct(Session $session) {
        $this->plugin = $session->getManager()->getPlugin();
        $this->session = $session;
    }

    public function updateScoreboard(Player $player) {
        $status = $this->getStatus();
        $this->lineTitle($player, " §c§lValiant");
        $this->lineCreate($player, 0, str_repeat(" ", 20));
        $this->lineCreate($player, 1, "§cSumo Event");
        $this->lineCreate($player, 2, str_repeat(" ", 5));
        $this->lineCreate($player, 3, "§cPlayers: §f" . $this->getScoreboardPlayers());
        $this->lineCreate($player, 4, "§cRound: §f" . $this->getRounds());
        $this->lineCreate($player, 5, str_repeat(" ", 4));
        $this->lineCreate($player, 6, "§cStatus: §f" . $status);
        $this->lineCreate($player, 7, str_repeat(" ", 3));
        $this->lineCreate($player, 8, "§cvaliantpvp.tk");
    }

    private function getScoreboardPlayers(): string {
        $match = $this->getSumoMatch();
        $tournament = $match->getTournament();
        $playerCount = count($tournament->getPlayers());

        if($match->getStage() instanceof WaitingStage) {
            return "$playerCount/?";
        }
        return "$playerCount/" . $tournament->getStartingPlayers();
    }

    private function getRounds(): string {
        return (string) $this->getSumoMatch()->getTournament()->getRounds();
    }

    private function getStatus(): string {
        $match = $this->getSumoMatch();
        $stage = $match->getStage();

        if($stage instanceof WaitingStage) {
            return "{YELLOW}Starting in " . gmdate("i:s", $stage->getCountdown());

        } elseif($stage instanceof PlayingStage) {

            if($stage->isFighting()) {
                $versus = $stage->getVersus();
                return
                    "{GOLD}" . $versus->getFirstPlayer()->getName() . " {YELLOW}vs " .
                    "{GOLD}" . $versus->getSecondPlayer()->getName();
            } else {
                return "{YELLOW}Waiting...";
            }

        } elseif($stage instanceof WinningStage) {;
            return "{YELLOW}Winner: {GOLD}" . $stage->getWinner()->getName();
        }
        return "{YELLOW}Loading...";
    }

    private function getSumoMatch(): ?Match {
        return $this->plugin->getMatchManager()->getMatchByLevel($this->session->getPlayer()->getLevel());
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
