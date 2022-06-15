<?php

declare(strict_types=1);

namespace Valiant\Command\Punishments;

use Valiant\Libs\Webhook\Embed;
use Valiant\Libs\Webhook\Message;
use Valiant\Libs\Webhook\Webhook;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;
use Valiant\Core;

class PBanCMD extends PluginCommand {

    private $plugin;

    public function __construct(Core $plugin) {
        parent::__construct("pban", $plugin);
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if (!$sender instanceof Player) {
            return false;
        }
        if (!$sender->hasPermission("valiant.staff")) {
            $sender->sendMessage("§cYou cannot execute this command.");
            return false;
        }
        if (!isset($args[0])) {
            $this->plugin->getStaffUtils()->permBanForm($sender);
            return false;
        }
        if ($this->plugin->getServer()->getPlayer($args[0]) === null) {
            $sender->sendMessage("§cPlayer not found.");
            return false;
        }
        $p = array_shift($args);
        $player = $this->plugin->getServer()->getPlayer($p);
        if ($this->plugin->getServer()->getIPBans()->isBanned($player->getName())) {
            $sender->sendMessage("§cThat player is already banned.");
            return true;
        }
        if ($player !== null && $player->isOnline()) {
            $this->plugin->getStaffUtils()->permBanPlayer($player, $sender, "unfair advantage or abuse");
            $this->plugin->getServer()->broadcastMessage("§7" . $player->getName() . " §cwas suspended from Valiant.\n§cReason: §7unfair advantage or abuse");
            $webHook = new Webhook(Core::BANLOGWEBHOOK);
            $msg = new Message();
            $embed = new Embed();
            $embed->setTitle("The ban hammer has spoken!");
            $embed->setColor(0xFF0000);
            $embed->setDescription("**Staff:** CONSOLE\n**Banned: **" . $p . "\n**Reason:** unfair advantage\n**Length:** permanent");
            $embed->setFooter("Valiant Practice");
            $msg->addEmbed($embed);
            $webHook->send($msg);
        } else {
            $sender->sendMessage("§cPlayer not found.");
        }
        return true;
    }
}
