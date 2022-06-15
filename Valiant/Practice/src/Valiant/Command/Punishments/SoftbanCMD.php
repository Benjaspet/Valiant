<?php

declare(strict_types=1);

namespace Valiant\Command\Punishments;

use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;
use Valiant\Core;
use Valiant\Libs\Webhook\Embed;
use Valiant\Libs\Webhook\Message;
use Valiant\Libs\Webhook\Webhook;

class SoftbanCMD extends PluginCommand {

    private $plugin;

    public function __construct(Core $plugin) {
        parent::__construct("softban", $plugin);
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
            $this->plugin->getStaffUtils()->softBanForm($sender);
            return true;
        }
        $nullPlayer = $this->plugin->getServer()->getPlayer($args[0]);
        if ($nullPlayer === null) {
            $nullPlayer = $args[0];
            $this->plugin->getServer()->getNameBans()->addBan($nullPlayer, "unfair advantage or abuse", null, $sender->getName());
            $this->plugin->getServer()->broadcastMessage("§c" . $sender->getName() . " removed " . $nullPlayer . " from Valiant.\n§cReason: §7unfair advantage or abuse");
            $webHook = new Webhook(Core::BANLOGWEBHOOK);
            $msg = new Message();
            $embed = new Embed();
            $embed->setTitle("The ban hammer has spoken!");
            $embed->setColor(0xFF0000);
            $embed->setDescription("**Staff:** " . $sender->getName() . "\n**Banned: **" . $nullPlayer . "\n**Reason:** unfair advantage or abuse\n**Type:** softban");
            $embed->setFooter("Valiant Practice");
            $msg->addEmbed($embed);
            $webHook->send($msg);
        } else {
            $this->plugin->getStaffUtils()->softBanForm($sender);
        }
        return true;
    }
}