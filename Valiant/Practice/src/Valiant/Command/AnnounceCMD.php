<?php

declare(strict_types=1);

namespace Valiant\Command;

use pocketmine\command\PluginCommand;
use pocketmine\command\CommandSender;
use Valiant\Core;

class AnnounceCMD extends PluginCommand {

    private $plugin;

    public function __construct(Core $plugin){
        parent::__construct("announce", $plugin);
        $this->plugin = $plugin;
        $this->setAliases(["ano", "broadcast"]);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if(!$sender->isOp()){
            $sender->sendMessage("§cYou cannot execute this command.");
            return false;
        }
        $message = implode(" ", $args);
        $this->plugin->getServer()->broadcastMessage("§l§d" . $message);
        return true;
    }
}