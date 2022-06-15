<?php

declare(strict_types=1);

namespace Valiant\Utils;

use pocketmine\Player;
use Valiant\Core;

class ClickUtil {

    private $plugin;
    private $clicks=[];

    public function __construct(Core $plugin){
        $this->plugin = $plugin;
    }
    public function isInArray(Player $player):bool{
        $name= $player->getName();
        return ($name !== null) and isset($this->clicks[$name]);
    }
    public function addToArray(Player $player){
        if(!$this->isInArray($player)){
            $this->clicks[$player->getName()]=[];
        }
    }
    public function removeFromArray(Player $player){
        if($this->isInArray($player)){
            unset($this->clicks[$player->getName()]);
        }
    }
    public function addClick(Player $player){
        array_unshift($this->clicks[$player->getName()], microtime(true));
        if(count($this->clicks[$player->getName()]) >= 100){
            array_pop($this->clicks[$player->getName()]);
        }
        $player->sendPopup("§7CPS: §c§l".$this->getCps($player));
    }
    public function getCps(Player $player, float $deltaTime=1.0, int $roundPrecision=1):float{
        if(!$this->isInArray($player) or empty($this->clicks[$player->getName()])){
            return 0.0;
        }
        $mt=microtime(true);
        return round(count(array_filter($this->clicks[$player->getName()], static function(float $t) use ($deltaTime, $mt):bool{
                return ($mt - $t) <= $deltaTime;
            })) / $deltaTime, $roundPrecision);
    }
}