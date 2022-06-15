<?php

declare(strict_types=1);

namespace Valiant\Command\Punishments;

use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;
use Valiant\Core;

class UnbanCMD extends PluginCommand {

    private $plugin;

    public function __construct(Core $plugin)
    {
        parent::__construct("unban", $plugin);
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if (!$sender instanceof Player) {
            return false;
        }
        if (!isset($args[0])) {
            $sender->sendMessage("§cPlease provide a player.");
            return false;
        }
        if (!$sender->hasPermission("valiant.staff")) {
            $sender->sendMessage("§cYou cannot execute this command.");
            return false;
        }
        if ($this->plugin->getStaffUtils()->unbanPlayer($args[0])) {
            $sender->sendMessage("§c" . $args[0] . " has been unbanned.");
            $this->plugin->getServer()->getIPBans()->remove($args[0]);
        } else {
            $sender->sendMessage("§c" . $args[0] . " is not banned.");
        }
        return true;
    }
}