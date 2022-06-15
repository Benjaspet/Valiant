<?php

declare(strict_types=1);

namespace Valiant\Command;

use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;
use Valiant\Core;
use Valiant\Libs\Webhook\Embed;
use Valiant\Libs\Webhook\Message;
use Valiant\Libs\Webhook\Webhook;

class ReportCMD extends PluginCommand {

    private $plugin;

    public function __construct(Core $plugin) {
        parent::__construct("report", $plugin);
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if (!$sender instanceof Player) {
            return false;
        }
        if (count($args) < 1) {
            $sender->sendMessage("§cUsage: §7/report {player}");
            return false;
        }
        $author = $sender;
        $target = $this->plugin->getServer()->getPlayer($args[0]);
        if ($target == null) {
            $sender->sendMessage("§cPlayer not found.");
            return false;
        }
        $webHook = new Webhook(Core::REPORTWEBHOOK);
        $msg = new Message();
        $embed = new Embed();
        $embed->setTitle("New player report!");
        $embed->setColor(0x518BBF);
        $embed->setDescription("**Author:** " . $author->getName() . "\n**Reported: **" . $target);
        $embed->setFooter("Valiant Practice");
        $msg->addEmbed($embed);
        $webHook->send($msg);
        foreach ($this->plugin->getServer()->getOnlinePlayers() as $online) {
            if ($online->hasPermission("valiant.staff")) {
                $online->sendMessage("§c" . $sender->getName() . " reported " . $this->plugin->getServer()->getPlayer($args[0]) . ".");
            }
        }
        $sender->sendMessage("§aYour report was sent successfully! A staff member will assist shortly.");
        return true;
    }
}