<?php

declare(strict_types=1);

namespace Valiant\Command;

use pocketmine\command\PluginCommand;
use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\protocol\PlayerListPacket;
use pocketmine\network\mcpe\protocol\types\PlayerListEntry;
use pocketmine\network\mcpe\protocol\types\SkinAdapterSingleton;
use pocketmine\Player;
use Valiant\Core;

class DisguiseCMD extends PluginCommand {

    private $plugin;

    public function __construct(Core $plugin) {
        parent::__construct("disguise", $plugin);
        $this->plugin = $plugin;
        $this->setAliases(["hide"]);
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
            if ($this->plugin->getUtils()->isDisguised($sender)) {
                $this->plugin->getUtils()->setDisguised($sender, false);
            }
            $names = $this->plugin->getUtils()->getFakeNames();
            $randomName = $names[array_rand($names)];
            foreach ($this->plugin->getServer()->getOnlinePlayers() as $online) {
                if ($sender->getDisplayName() == $randomName or $sender->getName() == $randomName) {
                    $sender->sendMessage("§cPlease try again. That name is already in use.");
                }
            }
            $this->plugin->getUtils()->setDisguised($sender, true);
            $sender->sendMessage("§aYou are now disguised as " . $randomName . ".");
            $sender->setDisplayName("§a" . $randomName);
            foreach ($this->plugin->getServer()->getOnlinePlayers() as $online) {
                $entry = new PlayerListEntry();
                $entry->uuid = $sender->getUniqueId();
                $packet = new PlayerListPacket();
                $packet->entries[] = $entry;
                $packet->type = PlayerListPacket::TYPE_REMOVE;
                $online->sendDataPacket($packet);
                $packet2 = new PlayerListPacket();
                $packet2->type = PlayerListPacket::TYPE_ADD;
                $packet2->entries[] = PlayerListEntry::createAdditionEntry($sender->getUniqueId(), $sender->getId(), $sender->getDisplayName(), SkinAdapterSingleton::get()->toSkinData($sender->getSkin()), "");
                $online->sendDataPacket($packet2);
                if ($online->hasPermission("valiant.staff")) {
                    $this->plugin->getStaffUtils()->sendStaffNotification("§cA staff member has disguised.");
                }
            }
        }
        if (isset($args[0])) {
            switch ($args[0]) {
                case "off":
                    if (!$this->plugin->getUtils()->isDisguised($sender)) {
                        $sender->sendMessage("§cYou are not disguised.");
                        return false;
                    }
                    $before = $sender->getDisplayName();
                    $this->plugin->getUtils()->setDisguised($sender, false);
                    $sender->setDisplayName($sender->getName());
                    $packet = new PlayerListPacket();
                    $packet->type = PlayerListPacket::TYPE_ADD;
                    $packet->entries[] = PlayerListEntry::createAdditionEntry($sender->getUniqueId(), $sender->getId(), $sender->getDisplayName(), SkinAdapterSingleton::get()->toSkinData($sender->getSkin()), $sender->getXuid());
                    foreach ($this->plugin->getServer()->getOnlinePlayers() as $online){
                        $online->sendDataPacket($packet);
                        if ($online->hasPermission("valiant.staff")){
                            $this->plugin->getStaffUtils()->sendStaffNotification("§cA staff member has disguised.");
                        }
                    }
                    $sender->sendMessage("§aDisguise disabled.");
                    break;
                default:
                    $sender->sendMessage("§cPlease provide an argument: off.");
            }
        }
        return true;
    }
}