<?php

namespace Valiant\Command\Punishments;

use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\Config;
use Valiant\Core;
use Valiant\Libs\Webhook\Embed;
use Valiant\Libs\Webhook\Message;
use Valiant\Libs\Webhook\Webhook;

class MuteCMD extends PluginCommand {

    private $plugin;

    public function __construct(Core $plugin) {
        parent::__construct("mute", $plugin);
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if ($sender instanceof Player && $sender->hasPermission("valiant.staff")) {
            if (isset($args[0])) {
                if (isset($args[1])) {
                    if (isset($args[2])) {
                        $config = new Config("plugin_data/Valiant/mutes.yml");
                        if ($player = $this->plugin->getServer()->getPlayer($args[0])) {
                            if ($player === $sender) {
                                $sender->sendMessage("§cYou cannot mute yourself.");
                                return true;
                            } else {
                                if ($config->get($args[0])) {
                                    $sender->sendMessage("§cThat player is already muted.");
                                    return true;
                                } else {
                                    if (!$sender instanceof Player) {
                                        $config->setNested($player->getName() . ".reason", $args[1]);
                                        $config->setNested($player->getName() . ".time", date("Y-m-d H:i:s"));
                                        $config->setNested($player->getName() . ".staff", $sender->getName());
                                        $config->save();
                                        $date = new \DateTime("+" . $args[2]);
                                        $date->setTimezone(new \DateTimeZone("Europe/Berlin"));
                                        $config->setNested($player->getName() . ".time", $date->format("Y-m-d H:i:s"));
                                        $config->save();
                                        $config->reload();
                                        $player->sendMessage("§cYou were temporarily muted!\nReason: §7" . $args[1]);
                                        $this->plugin->getServer()->broadcastMessage("§c" . $player->getName() . "§cwas temporarily muted!\n§cReason: §7" . $args[1] . "\n§cExpires: §7" . $config->getNested($player->getName() . ".time"));
                                        $webHook = new Webhook("https://discord.com/api/webhooks/815689044859158578/b9pu2I3eCP0J0KAbTy5EWfeu-YM6jxKLnx0XsqDCaomRSN7bINi4RTzmGbg19CHF3aYS");
                                        $msg = new Message();
                                        $embed = new Embed();
                                        $embed->setTitle("The ban hammer has spoken!");
                                        $embed->setColor(0x6AA84F);
                                        $embed->setDescription("**Staff:** " . $sender->getName() . "\n**Muted: **" . $player->getName() . "\n**Reason:** " . $args[1] . "\n**Expires:** " . $config->getNested($player->getName() . ".time"));
                                        $embed->setFooter("Valiant Practice");
                                        $msg->addEmbed($embed);
                                        $webHook->send($msg);
                                    } else {
                                        $config->setNested($player->getName() . ".reason", $args[1]);
                                        $config->setNested($player->getName() . ".time", date("Y-m-d H:i:s"));
                                        $config->setNested($player->getName() . ".staff", $sender->getName());
                                        $config->save();
                                        $date = new \DateTime("+" . $args[2]);
                                        $date->setTimezone(new \DateTimeZone("Europe/Berlin"));
                                        $config->setNested($player->getName() . ".time", $date->format("Y-m-d H:i:s"));
                                        $config->save();
                                        $config->reload();
                                        $player->sendMessage("§cYou were temporarily muted!\nReason: §7" . $args[1]);
                                        $this->plugin->getServer()->broadcastMessage("§c" . $player->getName() . "§cwas temporarily muted!\n§cReason: §7" . $args[1] . "\n§cExpires: §7" . $config->getNested($player->getName() . ".time"));
                                        $webHook = new Webhook("https://discord.com/api/webhooks/815689044859158578/b9pu2I3eCP0J0KAbTy5EWfeu-YM6jxKLnx0XsqDCaomRSN7bINi4RTzmGbg19CHF3aYS");
                                        $msg = new Message();
                                        $embed = new Embed();
                                        $embed->setTitle("The ban hammer has spoken!");
                                        $embed->setColor(0x6AA84F);
                                        $embed->setDescription("**Staff:** " . $sender->getName() . "\n**Muted: **" . $player->getName() . "\n**Reason:** " . $args[1] . "\n**Expires:** " . $config->getNested($player->getName() . ".time"));
                                        $embed->setFooter("Valiant Practice");
                                        $msg->addEmbed($embed);
                                        $webHook->send($msg);
                                    }
                                }
                            }
                        } else {
                            $sender->sendMessage("§cThat player is offline.");
                        }
                    } else {
                        $sender->sendMessage("§cUsage: §7/mute {player} {reason} {time}");
                    }
                } else {
                    $sender->sendMessage("§cUsage: §7/mute {player} {reason} {time}");
                }
            } else {
                $sender->sendMessage("§cUsage: §7/mute {player} {reason} {time}");
            }
        } else {
            $sender->sendMessage("§cYou cannot execute this command.");
        }
        return true;
    }
}