<?php

declare(strict_types=1);

namespace Valiant\Duels;

use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\Player;
use Valiant\Core;

class DuelAPI {

    private $plugin;

    public function __construct(Core $plugin) {
        $this->plugin = $plugin;
    }

    public function findDuel(Player $player, string $type, bool $ranked, string $map): void {
        foreach ($this->plugin->getDuelManager()->getDuels() as $duel) {
            if ($duel->getStatus() === true && $duel->getType() === $type && $ranked === $duel->getRanked()) {
                if ($duel->getRanked() === true) {
                    foreach ($duel->getPlayers() as $player1) {
                        if (abs($this->plugin->getPlayerManager()->getPlayer($player)->getElo() - $this->plugin->getPlayerManager()->getPlayerByName($player1)->getElo()) <= 100) {
                            $this->joinPlayer($player, $duel);
                            return;
                        }
                    }
                } else {
                    $this->joinPlayer($player, $duel);
                }
                return;
            }
        }
        $this->makeDuel($player, $type, $ranked, $map);
    }

    public function resetPlayer(Player $player): void
    {
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->setHealth($player->getMaxHealth());
        $player->removeAllEffects();
        $player->getCursorInventory()->clearAll();
        $player->setAbsorption(0);
        $player->setGamemode(2);
        $player->extinguish();
        $this->plugin->getUtils()->teleportToHub($player);
        $this->plugin->getKitUtil()->sendKit($player, "Lobby");
    }

    public function joinPlayer(Player $player, Duel $duel): void {
        $duel->addPlayer($player->getName());
        $duel->init();
    }

    public function makeDuel(Player $player, string $type, bool $ranked, string $map): void {
        $this->plugin->getDuelManager()->createDuel($player, $type, $ranked, $map);
    }

    public function checkDuel(Duel $duel): void
    {
        $players = $duel->getPlayers();
        if (count($players) === 1) {
            $this->endDuel($duel, reset($players));
        } else if (count($players) <= 0) {
            $this->endDuel($duel);
        }
    }

    public function eliminatePlayer(Duel $duel, Player $player): void
    {
        $duel->removePlayer($player->getName());
        $this->resetPlayer($player);
        $this->checkDuel($duel);
    }

    public function endDuel(Duel $duel, string $winner = null): void
    {

        $this->plugin->getDuelManager()->deleteDuel($duel->getId());
        $duel->endTask();
        $this->plugin->getGenerator()->deleteMap("game_" . $duel->getId());

        foreach ($duel->getPlayers() as $player){
            $player = $this->plugin->getServer()->getPlayer($player);
            $this->resetPlayer($player);
        }

        if (!$winner) return;

        if ($winner === "time") {
            return;
        }
    }
}