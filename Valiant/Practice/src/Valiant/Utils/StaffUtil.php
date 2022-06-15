<?php

declare(strict_types=1);

namespace Valiant\Utils;

use pocketmine\command\CommandSender;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\Player;
use pocketmine\utils\Config;
use Valiant\Core;
use Valiant\Libs\CustomForm;
use Valiant\Libs\SimpleForm;
use Valiant\Libs\Webhook\Embed;
use Valiant\Libs\Webhook\Message;
use Valiant\Libs\Webhook\Webhook;

class StaffUtil {

    private $plugin;
    public $freeze = array();
    private $playerlist;

    public function __construct(Core $plugin) {
        $this->plugin = $plugin;
    }

    public function vanish(Player $player, $bool = false) {
        if ($bool = true) {
            if ($player instanceof Player) {
                $player->setInvisible(true);
                $player->setGamemode(3);
                $this->plugin->getServer()->broadcastMessage("§8[§4-§8] §c" . $player->getName());
                $player->sendMessage("§aYou are now vanished.");
                $this->sendStaffNotification("§c" . $player->getName() . " has vanished.");
            }
        } else {
            $player->setInvisible(false);
            $player->setGamemode(2);
            $player->sendMessage("§cYou are no longer vanished.");
            $this->sendStaffNotification("§c" . $player->getName() . " has unvanished.");
        }
    }

    public function staffMode(Player $player, $bool = false) {
        if ($bool = true) {
            $this->vanish($player, true);
            $this->plugin->getKitUtil()->sendKit($player, "StaffMode");
        } else {
            $this->vanish($player, false);
            $this->plugin->getKitUtil()->sendKit($player, "Hub");
            $this->plugin->getUtils()->teleportToHub($player);
        }
    }

    public function sendStaffNotification($message): void {
        foreach ($this->plugin->getServer()->getOnlinePlayers() as $player) {
            if ($player instanceof Player && $player->hasPermission("valiant.staff")) {
                $player->sendPopup($message);
            }
        }
    }

    public function freezePlayer(Player $player, Player $staff) {
        if ($player instanceof Player) {
            if (!in_array($player->getName(), $this->freeze)) {
                $this->plugin->getServer()->broadcastMessage("§c".$player->getName()." has been frozen!");
                $player->sendMessage("§cYou have been frozen. Do not log out!");
                $player->setImmobile(true);
                $player->sendTitle("§c§lSTOP!", "§7You have been frozen!");
                $this->freeze[$player->getName()] = $player->getName();
            } else {
                $this->plugin->getServer()->broadcastMessage("§c".$player->getName()." has been unfrozen.");
                $player->sendMessage("§aYou can now move.");
                $player->setImmobile(false);
                unset($this->freeze[$player->getName()]);
            }
        } if (!$player) {$staff->sendMessage("§cCannot find the specified player.");}
    }

    public function kickPlayer(Player $player, Player $staff, string $reason) {
        if ($staff instanceof Player) {
            if ($player->hasPermission("valiant.staff")) {
                $player->kick($reason, false);
            } else {
                $staff->sendMessage("§cYou cannot execute this command.");
            }
        } else {
            $staff->sendMessage("§cPlease execute this command in-game.");
        }
    }

    public function unmutePlayer($player, CommandSender $staff): bool {
        $config = new Config("plugin_data/Valiant/mutes.yml");
        $config->remove($player);
        $config->save();
        $player = $this->plugin->getServer()->getPlayer($player);
        if (!$player) {
            return true;
        } else {
            $player->sendMessage("§cYou were unmuted by a staff member.");
        }
        return true;
    }

    public function unbanPlayer(string $name, bool $save = true) : bool{
        if(($key = array_search(strtolower($name), $this->plugin->permbans, true)) !== false){
            unset($this->plugin->permbans[$key]);
            if($save === true){
                $this->plugin->saveData();
            }
            return true;
        }
        return false;
    }

    // #### STAFF FORMS BELOW #### //

    public function softBanForm(Player $player) {
        $plist = [];
        $reasons = ["unfair advantage", "DDOS threats", "abuse", "ban evasion", "death threats"];
        foreach ($this->plugin->getServer()->getOnlinePlayers() as $p) {
            $plist[] = $p->getName();
        }
        $this->playerlist[$player->getName()] = $plist;
        $form = new CustomForm (function (Player $player, array $data = null) {
            if ($data === null) {
                return;
            }
            $index = $data[0];
            $playerName = $this->playerlist[$player->getName()] [$index];
            $target = $this->plugin->getServer()->getPlayer($playerName);
            $this->softBanOnlinePlayer($target, $player, "unfair advantage or abuse");
        });
        $form->setTitle("§8§lSOFTBAN FORM");
        $form->addDropdown("Select a player you'd like to softban. A softban bans a player's username indefinitely, but not their client or IP.\n\nNOTE: Those who abuse this form and/or its privileges will be punished!", $this->playerlist[$player->getName()]);
        $form->addDropdown("Reason for ban:", $reasons);
        $player->sendForm($form);
    }

    public function permBanForm(Player $player) {
        $plist = [];
        $reasons = ["unfair advantage", "DDOS threats", "abuse", "ban evasion", "death threats"];
        foreach ($this->plugin->getServer()->getOnlinePlayers() as $p) {
            $plist[] = $p->getName();
        }
        $this->playerlist[$player->getName()] = $plist;
        $form = new CustomForm (function (Player $player, array $data = null) {
            if ($data === null) {
                return;
            }
            $index = $data[0];
            $playerName = $this->playerlist[$player->getName()] [$index];
            $target = $this->plugin->getServer()->getPlayer($playerName);
            $this->permBanPlayer($target, $player, "unfair advantage or abuse");
        });
        $form->setTitle("§8§lPERMBAN FORM");
        $form->addDropdown("Select a player you'd like to permban. A permban bans a player's IP, client, and username indefinitely.\n\nNOTE: Those who abuse this form and/or its privileges will be punished!", $this->playerlist[$player->getName()]);
        $form->addDropdown("Reason for ban:", $reasons);
        $player->sendForm($form);
    }

    public function softBanNullPlayer(string $player, Player $staff, string $reason): bool {
        if (!$player instanceof Player) return false;
        if ($this->plugin->getServer()->getNameBans()->isBanned($player->getName())) {
            $staff->sendMessage("§cThat player is already banned.");
            return false;
        }
        $player->kick("§cYou were suspended from Valiant.\n§cStaff: §7" . $staff->getName() . "\n§cReason: §7" . $reason . "\n§cJoin our Discord: §7" . Core::DISCORD, false);
        sleep(1);
        $this->plugin->getServer()->getNameBans()->addBan($player->getName(), $reason, null, $staff->getName());
        $this->plugin->getServer()->broadcastMessage("§c" . $staff->getName() . " removed " . $player->getName() . " from Valiant.\n§cReason: §7" . $reason);
        $webHook = new Webhook(Core::BANLOGWEBHOOK);
        $msg = new Message();
        $embed = new Embed();
        $embed->setTitle("The ban hammer has spoken!");
        $embed->setColor(0xFF0000);
        $embed->setDescription("**Staff:** " . $staff->getName() . "\n**Banned: **" . $player->getName() . "\n**Reason:** " . $reason . "\n**Type:** softban");
        $embed->setFooter("Valiant Practice");
        $msg->addEmbed($embed);
        $webHook->send($msg);
        return true;
    }

    public function softBanOnlinePlayer(Player $player, Player $staff, string $reason): bool {
        if (!$player instanceof Player) return false;
        if ($this->plugin->getServer()->getNameBans()->isBanned($player->getName())) {
            $staff->sendMessage("§cThat player is already banned.");
            return false;
        }
        $player->kick("§cYou were suspended from Valiant.\n§cStaff: §7" . $staff->getName() . "\n§cReason: §7" . $reason . "\n§cJoin our Discord: §7" . Core::DISCORD, false);
        sleep(1);
        $this->plugin->getServer()->getNameBans()->addBan($player->getName(), $reason, null, $staff->getName());
        $this->plugin->getServer()->broadcastMessage("§c" . $staff->getName() . " removed " . $player->getName() . " from Valiant.\n§cReason: §7" . $reason);
        $webHook = new Webhook(Core::BANLOGWEBHOOK);
        $msg = new Message();
        $embed = new Embed();
        $embed->setTitle("The ban hammer has spoken!");
        $embed->setColor(0xFF0000);
        $embed->setDescription("**Staff:** " . $staff->getName() . "\n**Banned: **" . $player->getName() . "\n**Reason:** " . $reason . "\n**Type:** softban");
        $embed->setFooter("Valiant Practice");
        $msg->addEmbed($embed);
        $webHook->send($msg);
        return true;
    }

    public function permBanPlayer(Player $player, Player $staff, string $reason): bool {
        if (!$player instanceof Player) return false;
        if ($this->plugin->getServer()->getIPBans()->isBanned($player->getName())) {
            $staff->sendMessage("§cThat player is already banned.");
            return false;
        }
        $player->kick("§cYou were suspended from Valiant.\n§cStaff: §7" . $staff->getName() . "\n§cReason: §7" . $reason . "\n§cJoin our Discord: §7" . Core::DISCORD, false);
        sleep(1);
        $address = $player->getAddress();
        $this->plugin->getServer()->getIPBans()->addBan($address, $reason, null, $staff->getName());
        $this->plugin->getServer()->broadcastMessage("§c" . $staff->getName() . " removed " . $player->getName() . " from Valiant.\n§cReason: §7" . $reason);
        $webHook = new Webhook(Core::BANLOGWEBHOOK);
        $msg = new Message();
        $embed = new Embed();
        $embed->setTitle("The ban hammer has spoken!");
        $embed->setColor(0xFF0000);
        $embed->setDescription("**Staff:** " . $staff->getName() . "\n**Banned: **" . $player->getName() . "\n**Reason:** " . $reason . "\n**Type:** permanent");
        $embed->setFooter("Valiant Practice");
        $msg->addEmbed($embed);
        $webHook->send($msg);
        return true;
    }
}
