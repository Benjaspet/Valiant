<?php

declare(strict_types=1);

namespace Valiant\Listeners;

use pocketmine\event\Listener;
use pocketmine\Server;
use pocketmine\Player;
use pocketmine\item\Item;
use pocketmine\level\Location;
use pocketmine\math\Vector3;
use pocketmine\event\entity\EntityDamageEvent;
use Valiant\Core;
use Valiant\Libs\Webhook\Embed;
use Valiant\Libs\Webhook\Message;
use Valiant\Libs\Webhook\Webhook;

class ACListener implements Listener{

    private $plugin;
    private $reachCooldown=[];
    private $cpsCooldown=[];

    public function __construct(Core $plugin){
        $this->plugin=$plugin;
    }

    public function onEntityDamageByEntity(EntityDamageEvent $event){
        $player=$event->getEntity();
        $cause=$event->getCause();
        switch($cause){
            case EntityDamageEvent::CAUSE_ENTITY_ATTACK:
                $damager=$event->getDamager();
                if(!$player instanceof Player) return;
                if(!$damager instanceof Player) return;
                $damagerpos=$damager->getPosition() ?? new Vector3(0,0,0);
                $playerpos=$player->getPosition() ?? new Vector3(0,0,0);
                $distance=$damagerpos->distance($playerpos);
                $approxdist=6;
                $maxdist=7;
                $roundeddistance = round($distance, 3);
                if ($damager->getPing() >= 230){
                    $approxdist=$damager->getPing() / 34;
                    if($damager->getPing() >= 500){
                        $approxdist=$damager->getPing() / 50;
                    }
                }
                if ($distance > $maxdist){
                    $event->setCancelled();
                }
                if ($maxdist >= $distance and $distance >= $approxdist){
                    $this->plugin->getUtils()->addReachFlag($damager);
                    $message = "§8[§b!§8]§b " . $damager->getName() . " §8[§b" . $roundeddistance . " blocks§8] [§b" . $damager->getPing() . "ms§8]";
                    foreach ($this->plugin->getServer()->getOnlinePlayers() as $online){
                        if ($online->hasPermission("valiant.staff")){
                            $reach = 2;
                            if(!isset($this->reachCooldown[$online->getName()])){
                                $this->reachCooldown[$online->getName()]=time();
                            } else {
                                if ($reach > time() - $this->reachCooldown[$online->getName()]){
                                    $time = time() - $this->reachCooldown[$online->getName()];
                                } else {
                                    $this->reachCooldown[$online->getName()]=time();
                                    $online->sendMessage($message);
                                }
                            }
                        }
                    }
                }
                $cps = $this->plugin->getClickUtil()->getCps($damager);
                $approxcps = 23;
                $maxcps = 30;
                if($damager->getPing() >= 230){
                    $approxcps=25;
                    if($damager->getPing() >= 500){
                        $approxcps=27;
                    }
                }
                if (!$damager->isOp()){
                    if ($cps >= 55 && $damager->getPing() <= 200){
                        $damager->kick("§cYou were removed from Valiant.\n§cError code: §79J6H09X\n§cContact us: §7" . Core::DISCORD, false);
                        $this->plugin->getServer()->broadcastMessage("§cValiantAC removed " . $damager->getName() . " from Valiant.\n§cReason: autoclicker");
                        $this->plugin->getServer()->getNameBans()->addBan($damager->getName(), "unfair advantage or abuse", null, "ValiantAC");
                        $webHook = new Webhook(Core::BANLOGWEBHOOK);
                        $msg = new Message();
                        $embed = new Embed();
                        $embed->setTitle("The ban hammer has spoken!");
                        $embed->setColor(0xFF0000);
                        $embed->setDescription("**Staff:** " . "ANTICHEAT" . "\n**Banned: **" . $damager->getName() . "\n**Reason:** CHEAT DETECTION\n**Type:** softban");
                        $embed->setFooter("Valiant Practice");
                        $msg->addEmbed($embed);
                        $webHook->send($msg);
                    }
                }
                if ($cps >= $maxcps){
                    $event->setCancelled();
                }
                if($cps >= $approxcps){
                    $this->plugin->getUtils()->addCpsFlag($damager);
                    $message = "§8[§b!§8]§b " . $damager->getName() . " §8[§b" . $cps . " CPS§8] [§b" . $damager->getPing() . "ms§8]";
                    foreach($this->plugin->getServer()->getOnlinePlayers() as $online){
                        if($online->hasPermission("valiant.staff")){
                            $cps = 2;
                            if(!isset($this->cpsCooldown[$online->getName()])){
                                $this->cpsCooldown[$online->getName()]=time();
                            } else {
                                if($cps > time() - $this->cpsCooldown[$online->getName()]){
                                    $time = time() - $this->cpsCooldown[$online->getName()];
                                } else {
                                    $this->cpsCooldown[$online->getName()]=time();
                                    $online->sendMessage($message);
                                }
                            }
                        }
                    }
                }
                break;
            default:
                return;
                break;
        }
    }
}