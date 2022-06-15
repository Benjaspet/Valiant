<?php

namespace Valiant\Listeners;

use DateTime;
use pocketmine\command\Command;
use pocketmine\entity\Entity;
use pocketmine\entity\projectile\EnderPearl;
use pocketmine\entity\projectile\SplashPotion;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\ProjectileHitBlockEvent;
use pocketmine\event\entity\ProjectileHitEntityEvent;
use pocketmine\event\entity\ProjectileLaunchEvent;
use pocketmine\event\inventory\CraftItemEvent;
use pocketmine\event\inventory\FurnaceSmeltEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChangeSkinEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\item\Item;
use pocketmine\level\Position;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\Player;
use pocketmine\utils\Config;
use Valiant\Command\EventCMD;
use Valiant\Core;
use Valiant\PracticePlayer;
use Valiant\Task\Async\ProxyTask;
use Valiant\Task\ChatTask;
use Valiant\Task\EventTask;
use Valiant\Task\PearlTask;
use Valiant\Utils\Date\Countdown;

class PlayerListener implements Listener {

    private $plugin;
    public static $cooldown = [];
    public $skin = [];
    public $combat = [];

    public function __construct(Core $plugin) {
        $this->plugin = $plugin;
    }

    function onCraft(CraftItemEvent $event) {
        $event->setCancelled(true);
    }

    function onFurnace(FurnaceSmeltEvent $event) {
        $event->setCancelled(true);
    }

    public function onExhaust(PlayerExhaustEvent $event) {
        $cause = $event->getCause();
        $event->setCancelled(true);
    }

    public function onDeathMessage(PlayerDeathEvent $event) {
        $event->setDeathMessage(null);
    }

    public function onThrow(PlayerDropItemEvent $event) {
        $event->setCancelled(true);
    }

    public function onJoin(PlayerJoinEvent $event) {

        $player = $event->getPlayer();
        $event->setJoinMessage("§8[§2+§8] §a" . $player->getName());
        $this->plugin->getUtils()->preparePlayer($player);
        $this->skin[$player->getName()] = $player->getSkin();

        foreach ($this->plugin->getServer()->getLevels() as $level) {
            $this->plugin->getServer()->loadLevel($level->getName());
            $level->setTime(7000);
            $level->stopTime();
        }
    }

    public function onChangeSkin(PlayerChangeSkinEvent $event) {
        $player = $event->getPlayer();
        $this->skin[$player->getName()] = $player->getSkin();
    }

    public function onQuit(PlayerQuitEvent $event) {
        $player = $event->getPlayer();
        $event->setQuitMessage("§8[§4-§8] §c" . $player->getName());
        if ($player instanceof Player) {
            if ($this->plugin->getPlayerUtil()->isTagged($player)) {
                if ($event->getQuitReason() == "client disconnect") {
                    $this->plugin->getPlayerUtil()->setTagged($player, false);
                }
            }
        }
        if ($event->getQuitReason() == "timeout") {
            foreach ($this->plugin->getServer()->getOnlinePlayers() as $online) {
                if ($online->hasPermission("valiant.staff")) {
                    $this->plugin->getStaffUtils()->sendStaffNotification("§c" . $player->getName() . ": client disconnect.");
                }
            }
        }
    }

    // this is an old version, no mysql

    public function removeDeathScreen(EntityDamageEvent $event) {
        $victim = $event->getEntity();
        if ($event->getFinalDamage() >= $victim->getHealth()) {
            $event->setCancelled();
            if ($victim instanceof Player) {
                if ($event instanceof EntityDamageByEntityEvent) {
                    $attacker = $event->getDamager();
                    if ($attacker instanceof Player) {
                        $messages = ["quickied", "mopped", "blown to smithereens", "clowned", "handed an L", "crapped on", "necked", "rejected", "destroyed", "killed", "w-tapped", "comboed", "annihilated", "clipped", "railed", "taken ransom", "sent to rehab"];
                        $killerpots = 0;
                        $this->plugin->getPlayerUtil()->setTagged($victim, false);
                        $this->plugin->getPlayerUtil()->setTagged($attacker, false);
                        foreach ($attacker->getInventory()->getContents() as $pots) {
                            if ($pots->getId() === Item::SPLASH_POTION) $killerpots++;
                        }
                        if ($attacker->getLevel()->getFolderName() === "NoDebuff-FFA") {
                            $this->plugin->getServer()->broadcastMessage("§7" . $victim->getName() . " §7was " . $messages[array_rand($messages)] . " by " . $attacker->getName() . " §c[" . $killerpots . " pots]§7");
                        } else {
                            $this->plugin->getServer()->broadcastMessage("§7" . $victim->getName() . " §7was " . $messages[array_rand($messages)] . " by " . $attacker->getName() . "§7.");
                        }
                        $this->plugin->getUtils()->harpSound($attacker, 2);
                        if ($attacker->getLevel()->getFolderName() === "NoDebuff-FFA") {
                            $this->plugin->getKitUtil()->sendKit($attacker, "NoDebuff");
                            $attacker->sendMessage("§aYour kit has been refilled.");
                        } elseif ($attacker->getLevel()->getFolderName() === "Combo-FFA") {
                            $this->plugin->getKitUtil()->sendKit($attacker, "Combo");
                            $attacker->sendMessage("§aYour kit has been refilled.");
                        } elseif ($attacker->getLevel()->getFolderName() === "Gapple-FFA") {
                            $this->plugin->getKitUtil()->sendKit($attacker, "Gapple");
                            $attacker->sendMessage("§aYour kit has been refilled.");
                        }
                        $light = new AddActorPacket();
                        $light->type = "minecraft:lightning_bolt";
                        $light->entityRuntimeId = Entity::$entityCount++;
                        $light->metadata = [];
                        $light->motion = null;
                        $light->yaw = $victim->getYaw();
                        $light->pitch = $victim->getPitch();
                        $light->position = new Position($victim->getX(), $victim->getY(), $victim->getZ());
                        $sound = new PlaySoundPacket();
                        $sound->soundName = "ambient.weather.thunder";
                        $sound->x = $victim->getX();
                        $sound->y = $victim->getY();
                        $sound->z = $victim->getZ();
                        $sound->volume = 1;
                        $sound->pitch = 1;
                        $this->plugin->getServer()->broadcastPacket($victim->getLevel()->getPlayers(), $light);
                        $this->plugin->getServer()->broadcastPacket($victim->getLevel()->getPlayers(), $sound);
                        $this->plugin->getUtils()->spawnParticle($attacker, "smoke");
                        $victim->setCurrentTotalXp(0);
                        $victim->setXpProgress(0);
                        $victim->setXpLevel(0);
                        $this->plugin->getKitUtil()->sendKit($victim, "Lobby");
                        PracticePlayer::setKills($attacker, 1);
                        $this->plugin->getUtils()->teleportToHub($victim);
                    }
                }
            }
        }
        $entity = $event->getEntity();
        if ($entity instanceof Player and $event instanceof EntityDamageByEntityEvent) {
            $damager = $event->getDamager();
            if ($damager instanceof Player) {
                $this->plugin->getUtils()->knockBackVPlayer($entity);
            }
        }
    }

    public function onChat(PlayerChatEvent $event) {
        $player = $event->getPlayer();
        $config = new Config("plugin_data/Valiant/mutes.yml");
        if ($config->get($event->getPlayer()->getName())) {
            $unmute = $config->getNested($event->getPlayer()->getName() . ".time");
            $now = new \DateTime("now");
            if ($now < new \DateTime($unmute)) {
                $player->sendMessage("§cYou are currently muted.");
                $event->setCancelled(true);
            } else {
                $event->setCancelled(false);
            }
        }
        if (!$player->hasPermission("valiant.staff")) {
            if (!$this->plugin->getUtils()->isInChatCooldown()) {
                $this->plugin->getScheduler()->scheduleRepeatingTask(new ChatTask($this->plugin, $player), 20);
            } else {
                $event->setCancelled(true);
                $player->sendMessage("§cYou cannot chat that fast.");
                return;
            }
        }
    }

    public function onFall(EntityDamageEvent $event) {
        $player = $event->getEntity();
        if ($player instanceof Player) {
            switch ($event->getCause()) {
                case EntityDamageEvent::CAUSE_VOID:
                    $event->setCancelled(true);
                    $this->plugin->getUtils()->teleportToHub($player);
                    break;
                case EntityDamageEvent::CAUSE_FALL:
                    $event->setCancelled(true);
                    break;
                case EntityDamageEvent::CAUSE_ENTITY_ATTACK:
                    if ($player->getLevel()->getFolderName() === "Lobby") {
                        $event->setCancelled(true);
                        break;
                    }
            }
        }
    }

    public function removeVoid(PlayerMoveEvent $event) {
        $victim = $event->getPlayer();
        if (intval($victim->y) <= 1) {
            $light = new AddActorPacket();
            $light->type = "minecraft:lightning_bolt";
            $light->entityRuntimeId = Entity::$entityCount++;
            $light->metadata = [];
            $light->motion = null;
            $light->yaw = $victim->getYaw();
            $light->pitch = $victim->getPitch();
            $light->position = new Position($victim->getX(), $victim->getY(), $victim->getZ());
            $sound = new PlaySoundPacket();
            $sound->soundName = "ambient.weather.thunder";
            $sound->x = $victim->getX();
            $sound->y = $victim->getY();
            $sound->z = $victim->getZ();
            $sound->volume = 1;
            $sound->pitch = 1;
            $this->plugin->getServer()->broadcastPacket($victim->getLevel()->getPlayers(), $light);
            $this->plugin->getServer()->broadcastPacket($victim->getLevel()->getPlayers(), $sound);
            $victim->setCurrentTotalXp(0);
            $victim->setXpProgress(0);
            $victim->setXpLevel(0);
            $this->plugin->getUtils()->teleportToHub($victim);
        }
    }

    public function handleCombat(EntityDamageByEntityEvent $event) {
        $player = $event->getEntity();
        $damager = $event->getDamager();
        if ($player instanceof Player and $damager instanceof Player) {
            foreach ([$player, $damager] as $p) {
                if (!$p instanceof Player) return;
                if (!$this->plugin->getUtils()->isTagged($p)) {
                    $p->sendMessage("§cYou are currently in combat.");
                }
                $this->plugin->getUtils()->setTagged($p);
            }
        }
    }

    public function onInteract(PlayerInteractEvent $event): bool {
        $player = $event->getPlayer();
        $item = $event->getItem();
        $action = $event->getAction();
        if (!$player instanceof Player) return false;
        if ($action == PlayerInteractEvent::RIGHT_CLICK_AIR or $action == PlayerInteractEvent::RIGHT_CLICK_BLOCK) {
            switch ($item->getCustomName()) {
                case "§r§l§cHub":
                    $this->plugin->getUtils()->teleportToHub($player);
                    $this->plugin->getKitUtil()->sendKit($player, "Lobby");
                    break;
                case "§r§l§cSpectate":
                    $this->plugin->getFormUtil()->spectateForm($player);
                    break;
                case "§r§l§cDuels":
                    $player->sendMessage("§aComing soon!");
                    break;
                case "§r§l§cFFA Arenas":
                    $this->plugin->getFormUtil()->ffaForm($player);
                    break;
                case "§r§l§cSettings":
                    if ($player->hasPermission("valiant.premium")) {
                        $this->plugin->getFormUtil()->settingsForm($player);
                    } else {
                        $player->sendMessage("§cYou do not have access to this feature.");
                    }
                    break;
                case "§r§l§cEvents":
                    $this->plugin->getServer()->dispatchCommand($player, "event join");
                    break;
                case "§r§l§cStaff Portal":
                    $this->plugin->getFormUtil()->mainStaffForm($player);
                    break;
                case "§r§l§aExit StaffMode":
                    $this->plugin->getUtils()->teleportToHub($player);
                    $this->plugin->getKitUtil()->sendKit($player, "Lobby");
                    $this->plugin->getServer()->dispatchCommand($player, "staff off");
                    break;
                case "§r§l§eAdmin Tools":
                    if ($player->isOp()) {
                        $this->plugin->getFormUtil()->adminForm($player);
                    } else {
                        $player->sendMessage("§cYou are not an admin.");
                    }
                    break;
                case "§r§l§6Teleport":
                    $this->plugin->getFormUtil()->teleportForm($player);
                    break;
                case "§r§l§bFreeze a Player":
                    $this->plugin->getFormUtil()->freezeForm($player);
                    break;
            }
        }
        return true;
    }

    public function onPacketReceive(DataPacketReceiveEvent $event) {
        $packet = $event->getPacket();
        $player = $event->getPlayer();
        if ($packet::NETWORK_ID===InventoryTransactionPacket::NETWORK_ID and $packet->transactionType===InventoryTransactionPacket::TYPE_USE_ITEM_ON_ENTITY){
            $this->plugin->getClickUtil()->addToArray($player);
            $this->plugin->getClickUtil()->addClick($player);
        }
        if ($packet::NETWORK_ID === LevelSoundEventPacket::NETWORK_ID and $packet->sound === LevelSoundEventPacket::SOUND_ATTACK_NODAMAGE) {
            $this->plugin->getClickUtil()->addToArray($player);
            $this->plugin->getClickUtil()->addClick($player);
        }
    }

    public function onPearlThrow(ProjectileLaunchEvent $event) {
        $pearl = $event->getEntity();
        if ($pearl instanceof EnderPearl) {
            $player = $event->getEntity()->getOwningEntity();
            if ($player instanceof Player) {
                if (!isset(PlayerListener::$cooldown[$player->getName()])) {
                    PlayerListener::$cooldown[$player->getName()] = 1;
                    $timer = 151;
                    $this->plugin->getScheduler()->scheduleRepeatingTask(new PearlTask($this->plugin, $player, $timer), 2);
                } else {
                    $event->setCancelled(true);
                    $addedpearl = Item::get(368, 0, 1);
                    $player->getInventory()->setItem(1, $addedpearl);
                }
            }
        }
    }

    public function giveEnderPearl(PlayerInteractEvent $event) {
        $player = $event->getPlayer();
        if (isset(PlayerListener::$cooldown[$player->getName()])) {
            if ($event->getItem()->getId() == Item::ENDER_PEARL) {
                $event->setCancelled(true);
            }
        }
    }

    public function onDamage(EntityDamageEvent $event) {
        if ($event instanceof EntityDamageByEntityEvent) {
            $staff = $event->getDamager();
            $victim = $event->getEntity();
            if ($staff instanceof Player && $victim instanceof Player) {
                if ($staff->getInventory()->getItemInHand()->getName() === "§r§l§bFreeze a Player") {
                    $this->plugin->getServer()->dispatchCommand($staff, "freeze " . $victim->getName());
                }
            }
        }
    }

    public function onPot(ProjectileHitBlockEvent $event) {
        $player = $event->getEntity()->getOwningEntity();
        $pot = $event->getEntity();
        if ($player instanceof Player) {
            if ($pot instanceof SplashPotion) {
                if ($pot != null) {
                    switch (round($player->distance($pot), 0)) {
                        case 1:
                        case 0:
                            $player->setHealth($player->getHealth() + 8);
                            break;
                        case 2:
                            $player->setHealth($player->getHealth() + 7.5);
                            break;
                        case 3:
                            $player->setHealth($player->getHealth() + 6);
                            break;
                        default:
                            return;
                    }
                }
            }
        }
    }

    public function onPotHit(ProjectileHitEntityEvent $event) {
        $player = $event->getEntityHit();
        $pot = $event->getEntity();
        if ($player instanceof Player) {
            if ($pot != null) {
                if ($pot instanceof SplashPotion) {
                    $player->setHealth($player->getHealth() + 8);
                    $this->plugin->getUtils()->harpSound($player, 2);
                    $this->plugin->getUtils()->spawnParticle($player, "smoke");
                }
            }
        }
    }

    public function onProxyJoin(PlayerJoinEvent $event) {
        if (!$event->getPlayer()->hasPermission("valiant.staff")) {
            $this->plugin->getServer()->getAsyncPool()->submitTask(new ProxyTask($event->getPlayer()->getName(), $event->getPlayer()->getAddress()));
        }
    }

    public function onLogin(PlayerLoginEvent $event) {
        if ($this->plugin->getServer()->getIPBans()->isBanned($event->getPlayer()->getName())) {
            $event->setKickMessage("§cYou are suspended from Valiant.\n§cBanned by: §7CONSOLE\n§cReason: §7unfair advantage or abuse\n§cAppeal: §7" . Core::DISCORD);
            $event->setCancelled();
        }
        if ($this->plugin->getServer()->getNameBans()->isBanned($event->getPlayer()->getName())) {
            $event->setKickMessage("§cYou have been suspended from Valiant.\n§cBanned by: §7CONSOLE\n§cReason: §7unfair advantage or abuse\n§cAppeal: §7" . Core::DISCORD);
            $event->setCancelled(true);
        }
    }

    public function staffChat(PlayerChatEvent $event) {
        $player = $event->getPlayer();
        $message = $event->getMessage();
        if ($player->hasPermission("valiant.staff") and $message[0] == "!") {
            $event->setCancelled();
            foreach ($this->plugin->getServer()->getOnlinePlayers() as $online) {
                if ($online->hasPermission("valiant.staff")) {
                    $msg = str_replace("!", "", $message);
                    $online->sendMessage("§o§8[§cSTAFF§8] §c" . $player->getName() . ": §7" . $msg);
                }
            }
        }
    }

    public function onEntityDamage(EntityDamageByEntityEvent $event) {
        $damager = $event->getDamager();
        $victim = $event->getEntity();
        if ($damager->getLevel()->getFolderName() === "Sumo-Event") {
            if ($damager instanceof Player and $victim instanceof Player) {
                if (!in_array($damager->getName(), $this->plugin->getEventUtil()->fighting)) {
                    $event->setCancelled(true);
                }
                foreach([$victim, $damager] as $players) {
                    if (!$event->isCancelled()) {
                        if ($players instanceof Player) {
                            $this->plugin->getPlayerUtil()->setTagged($players, true);
                            $players->sendMessage("§cYou are currently in combat.");
                        }
                    }
                }
                if (in_array($victim, $this->plugin->getStaffUtils()->freeze[$victim->getName()])) {
                    $event->setCancelled(true);
                    $damager->sendMessage("§cYou cannot damage a frozen player.");
                }
                if (in_array($damager, $this->plugin->getStaffUtils()->freeze[$damager->getName()])) {
                    $event->setCancelled(true);
                    $damager->sendMessage("§cYou cannot damage players while frozen.");
                }
            }
        }
    }

    public function onCommandPreProcess(PlayerCommandPreprocessEvent $event) {
        $player=$event->getPlayer();
        $message=$event->getMessage();
        if ($player instanceof Player and $this->plugin->getPlayerUtil()->isTagged($player) and $message[0]==="/") {
            if (!$player->isOp()) {
                $event->setCancelled();
                $player->sendMessage("§cYou cannot execute commands in combat.");
            } else {
                return;
            }
        }
        if ($player instanceof Player and in_array($player, $this->plugin->getStaffUtils()->freeze[$player->getName()])) {
            $event->setCancelled();
            $player->sendMessage("§cYou cannot execute commands while frozen.");
        }
    }

    public function onMove(PlayerMoveEvent $event) {
        $player = $event->getPlayer();
        if ($player instanceof Player) {
            if ($player->getY() < 50) {
                $name = $player->getName();
                if(in_array($name, $this->plugin->getEventUtil()->fighting)) {
                    $this->plugin->getEventUtil()->removePlayer($name);
                    $this->plugin->getEventUtil()->roundinprogress = false;
                    $this->plugin->getScheduler()->scheduleRepeatingTask(new EventTask($this->plugin), 20);
                    $sumomap = $this->plugin->getServer()->getLevelByName("Sumo-Event");
                    $pos = new Position(9984.55, 93, 10003.49, $sumomap);
                    $player->teleport($pos);
                    $light = new AddActorPacket();
                    $light->type = "minecraft:lightning_bolt";
                    $light->entityRuntimeId = Entity::$entityCount++;
                    $light->metadata = [];
                    $light->motion = null;
                    $light->yaw = $player->getYaw();
                    $light->pitch = $player->getPitch();
                    $light->position = new Position($player->getX(), $player->getY(), $player->getZ());
                    $sound = new PlaySoundPacket();
                    $sound->soundName = "ambient.weather.thunder";
                    $sound->x = $player->getX();
                    $sound->y = $player->getY();
                    $sound->z = $player->getZ();
                    $sound->volume = 1;
                    $sound->pitch = 1;
                    $this->plugin->getServer()->broadcastPacket($player->getLevel()->getPlayers(), $light);
                    $this->plugin->getServer()->broadcastPacket($player->getLevel()->getPlayers(), $sound);
                    $player->setCurrentTotalXp(0);
                    $player->setXpProgress(0);
                    $player->setXpLevel(0);
                    $player->removeAllEffects();
                }
            }
        }
    }
}