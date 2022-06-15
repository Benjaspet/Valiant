<?php

declare(strict_types=1);

namespace Valiant\Command;

use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;
use Valiant\Core;

class RekitCMD extends PluginCommand {

    private $plugin;

    public function __construct(Core $plugin) {
        parent::__construct("rekit", $plugin);
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if (!$sender instanceof Player) {
            return false;
        }
        if ($this->plugin->getUtils()->getFFA($sender) === "NoDebuff-FFA") {
            $this->plugin->getKitUtil()->sendKit($sender, "NoDebuff");
        } elseif ($this->plugin->getUtils()->getFFA($sender) === "Gapple-FFA") {
            $this->plugin->getKitUtil()->sendKit($sender, "Gapple");
        } elseif ($this->plugin->getUtils()->getFFA($sender) === "Combo-FFA") {
            $this->plugin->getKitUtil()->sendKit($sender, "Combo");
        } elseif ($this->plugin->getUtils()->getFFA($sender) === "Resistance-FFA") {
            $this->plugin->getKitUtil()->sendKit($sender, "Resistance");
        }
        return true;
    }
}