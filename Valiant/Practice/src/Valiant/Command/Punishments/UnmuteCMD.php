<?php

declare(strict_types=1);

namespace Valiant\Command\Punishments;

use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\utils\Config;
use Valiant\Core;

class UnmuteCMD extends PluginCommand {

    private $plugin;

    public function __construct(Core $plugin) {
        parent::__construct("unmute", $plugin);
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if ($sender->hasPermission("valiant.staff")) {
            if (isset($args[0])) {
                $config = new Config("plugin_data/Valiant/mutes.yml");
                if ($config->get($args[0])) {
                    $this->plugin->getStaffUtils()->unmutePlayer($args[0], $sender);
                    $sender->sendMessage("§a" . $args[0] . " has been unmuted.");
                } else {
                    $sender->sendMessage("§cThat player is not muted.");
                }
            } else {
                $sender->sendMessage("§cUsage: §7/unmute {player}");
            }
        } else {
            $sender->sendMessage("§cYou cannot execute this command.");
        }
        return true;
    }
}
