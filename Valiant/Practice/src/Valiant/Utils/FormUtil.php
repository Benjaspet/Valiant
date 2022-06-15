<?php

declare(strict_types=1);

namespace Valiant\Utils;

use pocketmine\command\CommandSender;
use pocketmine\entity\Skin;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\utils\Utils;
use Valiant\Core;
use Valiant\Libs\CustomForm;
use Valiant\Libs\SimpleForm;
use Valiant\Libs\Webhook\Embed;
use Valiant\Libs\Webhook\Message;
use Valiant\Libs\Webhook\Webhook;

class FormUtil {

    private $plugin;
    private $playerlist;
    private $targetPlayer = [];

    public function __construct(Core $plugin) {
        $this->plugin = $plugin;
    }

    public function teleportForm(Player $player) {
        $plist = [];
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
            $playerTarget = $this->plugin->getServer()->getPlayer($playerName);
            $player->teleport($playerTarget->getLocation());
        });
        $form->setTitle("§8§lTELEPORT MENU");
        $form->addDropdown("Select a player you'd like to teleport to. \n\nNOTE: Those who abuse this form and/or its priviledges will be punished!", $this->playerlist[$player->getName()]);
        $player->sendForm($form);
    }

    public function statusForm(Player $player): SimpleForm{
        $form = new SimpleForm (function (Player $event, $data) {
            $player = $event->getPlayer();
            if ($data === null) {
                return;
            }
            switch ($data) {
                case 0:
                    $this->adminForm($player);
                    break;
            }
        });
        $server = $player->getServer();
        $time = microtime(true) - \pocketmine\START_TIME;
        $seconds = floor($time % 60);
        $minutes = null;
        $hours = null;
        $days = null;
        if($time >= 60){
            $minutes = floor(($time % 3600) / 60);
            if($time >= 3600){
                $hours = floor(($time % (3600 * 24)) / 3600);
                if($time >= 3600 * 24){
                    $days = floor($time / (3600 * 24));
                }
            }
        }
        $uptime = ($minutes !== null ?
                ($hours !== null ?
                    ($days !== null ?
                        "$days days "
                        : "") . "$hours hours "
                    : "") . "$minutes minutes "
                : "") . "$seconds seconds";
        $currenttps = $server->getTicksPerSecond();
        $averagetps = $server->getTicksPerSecondAverage();
        $cpu = $server->getTickUsage();
        $upload = round($server->getNetwork()->getUpload() / 1024, 2) . " KB/s";
        $download = round($server->getNetwork()->getDownload() / 1024, 2) . " KB/s";
        $threadcount = Utils::getThreadCount();
        $mUsage = Utils::getMemoryUsage(true);
        $mainthreadmem = number_format(round(($mUsage[0] / 1024) / 1024, 2)) . " MB";
        $maxmem = number_format(round(($mUsage[2] / 1024) / 1024, 2)) . " MB";

        $form->setTitle("§l§8SERVER STATUS");
        $form->setContent("§aUptime: §f" . $uptime . "\n§aCurrent TPS: §f" . $currenttps . "\n§aAverage TPS: §f" . $averagetps .  "\n§aCPU Usage: §f" . $cpu . "%\n§aUpload: §f" . $upload . "\n§aDownload: §f" . $download . "\n§aThreads: §f" . $threadcount . "\n§aMain Thread Memory: §f" . $mainthreadmem . "\n§aMax Memory: §f" . $maxmem . "\n ");
        $form->addButton("§7Go back");
        $player->sendForm($form);
        return $form;
    }

    public function adminForm(Player $player): SimpleForm {
        $form = new SimpleForm (function (Player $event, $data) {
            $player = $event->getPlayer();
            if ($data === null) {
                return;
            }
            switch ($data) {
                case 0:
                    $this->statusForm($player);
                    break;
                case 1;
                    $this->plugin->getServer()->dispatchCommand($player, "restart");
                    break;
                case 2;
                    $this->gamemodeForm($player);
                    break;
            }
        });
        $form->setTitle("§l§8ADMIN FORM");
        $form->setContent("");
        $form->addButton("§l§8VIEW SERVER STATUS");
        $form->addButton("§l§8RESTART THE SERVER");
        $form->addButton("§l§8CHANGE GAMEMODE");
        $player->sendForm($form);
        return $form;
    }

    public function mainStaffForm(Player $player): SimpleForm {
        $form = new SimpleForm (function (Player $event, $data) {
            $player = $event->getPlayer();
            if ($data === null) {
                return;
            }
            switch ($data) {
                case 0:
                    $this->freezeForm($player);
                    break;
                case 1:
                    $this->teleportForm($player);
                    break;
                case 2:
                    $this->spectateForm($player);
                    break;
                case 3:
                    $this->kickForm($player);
                    break;
                case 4;
                    $this->plugin->getStaffUtils()->softBanForm($player);
                    break;
                case 5;
                    $this->plugin->getStaffUtils()->permBanForm($player);
                    break;
            }
        });
        $form->setTitle("§l§8STAFF PORTAL");
        $form->setContent("");
        $form->addButton("§8Freeze/Screenshare");
        $form->addButton("§8Teleport");
        $form->addButton("§8Spectate");
        $form->addButton("§8Kick");
        $form->addButton("§8Softban");
        $form->addButton("§8Permanent Ban");
        $player->sendForm($form);
        return $form;
    }

    public function settingsForm(Player $player): SimpleForm {
        $form = new SimpleForm (function (Player $event, $data) {
            $player = $event->getPlayer();
            if ($data === null) {
                return;
            }
            switch ($data) {
                case 0:
                    $this->plugin->getServer()->dispatchCommand($player, "cape");
                    break;
                case 1;
                    $this->plugin->getServer()->dispatchCommand($player, "particles");
                    break;
                case 2:
                    $this->plugin->getUtils()->cosmeticsForm($player);
                    break;
            }
        });
        $form->setTitle("§l§8SETTINGS FORM");
        $form->setContent("");
        $form->addButton("Capes");
        $form->addButton("Particles");
        $form->addButton("Settings");
        $player->sendForm($form);
        return $form;
    }

    public function gamemodeForm(Player $player): SimpleForm {
        $form = new SimpleForm (function (Player $event, $data) {
            $player = $event->getPlayer();
            if ($data === null) {
                return;
            }
            switch ($data) {
                case 0:
                    $player->removeAllEffects();
                    $player->setGamemode(1);
                    break;
                case 1;
                    $player->removeAllEffects();
                    $player->setGamemode(0);
                    break;
                case 2;
                    $player->removeAllEffects();
                    $player->setGamemode(2);
                    break;
                case 3:
                    $player->removeAllEffects();
                    $player->setGamemode(3);
                    break;
            }
        });
        $form->setTitle("§l§8GAMEMODE UI");
        $form->setContent("");
        $form->addButton("§l§8CREATIVE");
        $form->addButton("§l§8SURVIVAL");
        $form->addButton("§l§8ADVENTURE");
        $form->addButton("§l§8SPECTATOR");
        $player->sendForm($form);
        return $form;
    }

    public function freezeForm(Player $player) {
        $plist = [];
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
            $this->plugin->getStaffUtils()->freezePlayer($target, $player);
        });
        $form->setTitle("§8§lFREEZE/UNFREEZE FORM");
        $form->addDropdown("Select a player you'd like to freeze/unfreeze. \n\nNOTE: Those who abuse this form and/or its privileges will be punished!", $this->playerlist[$player->getName()]);
        $player->sendForm($form);
    }

    public function kickForm(Player $player) {
        $plist = [];
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
            if ($playerName == $player->getName()) {
                $player->sendMessage("§cYou cannot kick yourself.");
                return;
            }
            $target = $this->plugin->getServer()->getPlayer($playerName);
            $target->kick( "§cYou were kicked from the game.\n§cReason: §7interrupting or abuse\n§cContact us: §7" . Core::DISCORD, false);
            $this->plugin->getServer()->broadcastMessage("§c" . $player->getName() . " removed" . $target->getName() . " from Valiant.\n§cReason: §7unfair advantage or abuse");
            $webHook = new Webhook(Core::KICKWEBHOOK);
            $msg = new Message();
            $embed = new Embed();
            $embed->setTitle("Player has been kicked!");
            $embed->setColor(0xD2D231);
            $embed->setDescription("**Staff:** " . $player->getName() . "\n**Kicked: **" . $target->getName());
            $embed->setFooter("Valiant Practice");
            $msg->addEmbed($embed);
            $webHook->send($msg);
        });
        $form->setTitle("§8§lKICK FORM");
        $form->addDropdown("Select a player you'd like to kick. \n\nNOTE: Those who abuse this form and/or its privileges will be punished!", $this->playerlist[$player->getName()]);
        $player->sendForm($form);
    }

    public function ffaForm(Player $player): SimpleForm {
        $form = new SimpleForm (function (Player $event, $data) {
            $player = $event->getPlayer();

            if ($data === null) {
                return;
            }
            switch ($data) {
                case 0:
                    $this->plugin->getKitUtil()->sendKit($player, "NoDebuff");
                    $player->teleport($this->plugin->getServer()->getLevelByName("NoDebuff-FFA")->getSpawnLocation());
                    break;
                case 1;
                    $this->plugin->getKitUtil()->sendKit($player, "Gapple");
                    $player->teleport($this->plugin->getServer()->getLevelByName("Gapple-FFA")->getSpawnLocation());
                    break;
                case 2:
                    $this->plugin->getKitUtil()->sendKit($player, "Combo");
                    $player->teleport($this->plugin->getServer()->getLevelByName("Combo-FFA")->getSpawnLocation());
                    break;
                case 3:
                    $this->plugin->getKitUtil()->sendKit($player, "Resistance");
                    $player->teleport($this->plugin->getServer()->getLevelByName("Resistance-FFA")->getSpawnLocation());
                    break;
            }
        });
        $ndffa = $this->plugin->getServer()->getLevelByName("NoDebuff-FFA");
        $ndffacount = count($ndffa->getPlayers());
        $cmbffa = $this->plugin->getServer()->getLevelByName("Combo-FFA");
        $cmbffacount = count($cmbffa->getPlayers());
        $gappleffa = $this->plugin->getServer()->getLevelByName("Gapple-FFA");
        $gappleffacount = count($gappleffa->getPlayers());
        $resffa = $this->plugin->getServer()->getLevelByName("Resistance-FFA");
        $rescount = count($resffa->getPlayers());
        $form->setTitle("§l§8FFA ARENAS");
        $form->setContent("Select an Arena:");
        $form->addButton("§8NoDebuff\n§r§8Playing: " . $ndffacount . "", 0, "textures/items/potion_bottle_splash_heal");
        $form->addButton("§8Gapple\n§r§8Playing: " . $gappleffacount . "", 0, "textures/items/apple_golden");
        $form->addButton("§8Combo\n§r§8Playing: " . $cmbffacount . "", 0, "textures/items/fish_pufferfish_raw");
        $form->addButton("§8Resistance\n§r§8Playing: " . $rescount . "", 0, "textures/items/suspicious_stew");
        $player->sendForm($form);
        return $form;
    }

    public function spectateForm(Player $player): SimpleForm {
        $form = new SimpleForm (function (Player $event, $data) {
            $player = $event->getPlayer();

            if ($data === null) {
                return;
            }
            switch ($data) {
                case 0:
                    $player->getInventory()->clearAll();
                    $player->setHealth(20);
                    $player->setFood(20);
                    $player->teleport($this->plugin->getServer()->getLevelByName("NoDebuff-FFA")->getSafeSpawn());
                    $hubitem = Item::get(345, 0, 1);
                    $hubitem->setCustomName("§r§l§cHub");
                    $player->getInventory()->setItem(4, $hubitem);
                    $player->setGamemode(3);
                    break;
                case 1:
                    $player->getInventory()->clearAll();
                    $player->setHealth(20);
                    $player->setFood(20);
                    $player->teleport($this->plugin->getServer()->getLevelByName("Combo-FFA")->getSafeSpawn());
                    $hubitem = Item::get(345, 0, 1);
                    $hubitem->setCustomName("§r§l§cHub");
                    $player->getInventory()->setItem(4, $hubitem);
                    $player->setGamemode(3);
                    break;
                case 2:
                    $player->getInventory()->clearAll();
                    $player->setHealth(20);
                    $player->setFood(20);
                    $player->teleport($this->plugin->getServer()->getLevelByName("Resistance-FFA")->getSafeSpawn());
                    $hubitem = Item::get(345, 0, 1);
                    $hubitem->setCustomName("§r§l§cHub");
                    $player->getInventory()->setItem(4, $hubitem);
                    $player->setGamemode(3);
                    break;
            }
        });
        $ndffa = $this->plugin->getServer()->getLevelByName("NoDebuff-FFA");
        $ndffacount = count($ndffa->getPlayers());
        $cmbffa = $this->plugin->getServer()->getLevelByName("Combo-FFA");
        $cmbffacount = count($cmbffa->getPlayers());
        $resffa = $this->plugin->getServer()->getLevelByName("Resistance-FFA");
        $rescount = count($resffa->getPlayers());
        $form->setTitle("§l§8SPECTATE MENU");
        $form->setContent("Select an Arena:");
        $form->addButton("§8NoDebuff\n§r§8Playing: " . $ndffacount . "", 0, "textures/items/potion_bottle_splash_heal");
        $form->addButton("§8Combo\n§r§8Playing: " . $cmbffacount . "", 0, "textures/items/fish_pufferfish_raw");
        $form->addButton("§8Resistance\n§r§8Playing: " . $rescount . "", 0, "textures/items/suspicious_stew");
        $player->sendForm($form);
        return $form;
    }

    /*public function capesForm(Player $player) : SimpleForm {
        $form = new SimpleForm(function (Player $player, $data) {
            $result = $data;
            if ($result == null) {
                return true;
            }
            switch ($result) {
                case 0:
                    return true;
                case 1:
                    $oldSkin = $player->getSkin();
                    $setCape = new Skin($oldSkin->getSkinId(), $oldSkin->getSkinData(), "", $oldSkin->getGeometryName(), $oldSkin->getGeometryData());
                    $player->setSkin($setCape);
                    $player->sendSkin();
                    $player->sendMessage("§cYou removed your cape.");
                    return true;
                case 2:
                    $oldSkin = $player->getSkin();
                    $capeData = $this->plugin->getUtils()->createCape("valiant");
                    $setCape = new Skin($oldSkin->getSkinId(), $oldSkin->getSkinData(), $capeData, $oldSkin->getGeometryName(), $oldSkin->getGeometryData());
                    $player->setSkin($setCape);
                    $player->sendSkin();
                    $player->sendMessage("§aValiant cape equipped.");
                    break;
                case 3:
                    $oldSkin = $player->getSkin();
                    $capeData = $this->plugin->getUtils()->createCape("birthday");
                    $setCape = new Skin($oldSkin->getSkinId(), $oldSkin->getSkinData(), $capeData, $oldSkin->getGeometryName(), $oldSkin->getGeometryData());
                    $player->setSkin($setCape);
                    $player->sendSkin();
                    $player->sendMessage("§aBirthday cape equipped.");
                    break;
                case 4:
                    $oldSkin = $player->getSkin();
                    $capeData = $this->plugin->getUtils()->createCape("developer");
                    $setCape = new Skin($oldSkin->getSkinId(), $oldSkin->getSkinData(), $capeData, $oldSkin->getGeometryName(), $oldSkin->getGeometryData());
                    $player->setSkin($setCape);
                    $player->sendSkin();
                    $player->sendMessage("§aDeveloper cape equipped.");
                    break;
                case 5:
                    $oldSkin = $player->getSkin();
                    $capeData = $this->plugin->getUtils()->createCape("clown");
                    $setCape = new Skin($oldSkin->getSkinId(), $oldSkin->getSkinData(), $capeData, $oldSkin->getGeometryName(), $oldSkin->getGeometryData());
                    $player->setSkin($setCape);
                    $player->sendSkin();
                    $player->sendMessage("§aClown cape equipped.");
                    break;
            }
            return true;
        });
        $form->setTitle("§l§8CAPES FORM");
        $form->setContent("Please select a cape.");
        $form->addButton("§cClose", 0);
        $form->addButton("§cRemove Cape", 1);
        $form->addButton("Valiant Practice", 2);
        $form->addButton("Birthday", 3);
        $form->addButton("Developer", 4);
        $form->addButton("Clown", 5);
        $form->sendToPlayer($player);
        return $form;
    }*/
}
