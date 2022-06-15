<?php

declare(strict_types=1);

namespace Valiant\Utils;

use pocketmine\Player;
use Valiant\Core;
use Valiant\Libs\Webhook\Embed;
use Valiant\Libs\Webhook\Message;
use Valiant\Libs\Webhook\Webhook;

class StringUtil {

    private $plugin;

    public function __construct(Core $plugin) {
        $this->plugin = $plugin;
    }

    public function getPermIpBanUsage(): string {
        return "§cUsage: §7/ban-ip {player} {duration} {reason}";
    }

    public function getTbanIpUsage(string $message): string {
        return "§cUsage: §7/tban-ip {player} {duration} {reason}";
    }

    public function getPermBanKickMessage(): string {
        return "§cYou have been suspended from Valiant.\n§cBanned by: §7CONSOLE\n§cReason: §7unfair advantage or abuse\n§cAppeal: §7" . Core::DISCORD;
    }

    public function sendBanWebhook(string $title, string $description, string $footer, int $color) {
        $webhook = new Webhook("https://discord.com/api/webhooks/811800957037969438/iJMSA9QbxmX2Yn6NKvVwQIGfZ5iJ9J0HmBOf10V-YzR7XtuZRMkyQxd1IXrk9L8ERLP4");
        $msg = new Message();
        $embed = new Embed();
        $embed->setTitle($title);
        $embed->setColor($color);
        $embed->setDescription($description);
        $embed->setFooter($footer);
        $msg->addEmbed($embed);
        $webhook->send($msg);
    }

    public function sendReportWebhook(string $title, string $description, string $footer, int $color) {
        $webhook = new Webhook("https://discord.com/api/webhooks/811801783001022464/rZi7NhNtbILDYUfv6pu3Lm8-rsTb0MgJQCCflaS18nmwadqjgdXo636xSgnbQeTntUkR");
        $msg = new Message();
        $embed = new Embed();
        $embed->setTitle($title);
        $embed->setColor($color);
        $embed->setDescription($description);
        $embed->setFooter($footer);
        $msg->addEmbed($embed);
        $webhook->send($msg);
    }
}