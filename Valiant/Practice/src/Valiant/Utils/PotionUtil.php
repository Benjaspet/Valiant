<?php

declare(strict_types=1);

namespace Valiant\Utils;

use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\entity\Living;
use pocketmine\entity\Attribute;
use pocketmine\entity\projectile\Throwable;
use pocketmine\entity\projectile\Projectile;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\event\entity\ProjectileHitEntityEvent;
use pocketmine\event\entity\ProjectileHitBlockEvent;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\level\Level;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\item\Potion as ItemPotion;
use pocketmine\utils\Random;
use pocketmine\utils\Color;
use pocketmine\level\particle\FlameParticle;
use Valiant\Core;


class PotionUtil extends Projectile{

    public const NETWORK_ID=self::SPLASH_POTION;

    public $height=0.1;
    public $width=0.1;
    protected $gravity=0.05;
    protected $drag=0.01;

    private $hasSplashed=false;
    private $plugin;

    public function __construct(Core $plugin, Level $level, CompoundTag $nbt, ?Entity $owner=null){
        parent::__construct($level, $nbt, $owner);
        $this->plugin = $plugin;
        if($owner===null) return;
        $this->setPosition($this->add(0, $owner->getEyeHeight()));

    }
    protected function initEntity():void{
        parent::initEntity();
        $this->setPotionId($this->namedtag->getShort("PotionId", 22));
    }
    public function saveNBT():void{
        parent::saveNBT();
        $this->namedtag->setShort("PotionId", $this->getPotionId());
    }
    public function getResultDamage():int{
        return -1;
    }
    public function getPotionId():int{
        return $this->propertyManager->getShort(self::DATA_POTION_AUX_VALUE) ?? 22;
    }
    public function setPotionId(int $id):void{
        $this->propertyManager->setShort(self::DATA_POTION_AUX_VALUE, $id);
    }
    public function onHit(ProjectileHitEvent $event):void{
        $effects=$this->getPotionEffects();
        $owner=$this->getOwningEntity();
        $hasEffects=true;
        $color="default";
        if(count($effects)===0){
            $colors=[new Color(0x38, 0x5d, 0xc6)];
            $hasEffects=false;
        }else{
            if($owner instanceof Player) $color=$this->plugin->getUtils()->potSplashColor($owner);
            switch($color){
                case "default":
                    $colors=[new Color(255, 0, 0)];
                    break;
                case "pink":
                    $colors=[new Color(250, 10, 226)];
                    break;
                case "cyan":
                    $colors=[new Color(4, 248, 255)];
                    break;
                case "green":
                    $colors=[new Color(4, 255, 55)];
                    break;
                case "yellow":
                    $colors=[new Color(248, 255, 0)];
                    break;
                case "orange":
                    $colors=[new Color(255, 128, 0)];
                    break;
                default:
                    $colors=[new Color(0xf8, 0x24, 0x23)];
                    break;
            }
            $hasEffects=true;
        }
        $this->level->broadcastLevelEvent($this, LevelEventPacket::EVENT_PARTICLE_SPLASH, Color::mix(...$colors)->toARGB());
        $this->level->broadcastLevelSoundEvent($this, LevelSoundEventPacket::SOUND_GLASS);
        if($hasEffects){
            foreach($this->getLevel()->getNearbyEntities($this->getBoundingBox()->expand(1.7, 5.7, 1.7)) as $nearby){
                if($nearby instanceof Player and $nearby->isAlive()){
                    $multiplier= 1 - (sqrt($nearby->distanceSquared($this)) / 6.15);
                    if($multiplier > 0.578) $multiplier=0.578;
                    if($event instanceof ProjectileHitEntityEvent and $nearby===$event->getEntityHit()){
                        $multiplier=0.580;
                    }
                    foreach($this->getPotionEffects() as $effect){
                        $nearby->heal(new EntityRegainHealthEvent($nearby, (4 << $effect->getAmplifier()) * $multiplier * 1.75, EntityRegainHealthEvent::CAUSE_CUSTOM));
                    }
                }
            }
        }
    }
    public function getPotionEffects():array{
        return ItemPotion::getPotionEffectsById($this->getPotionId());
    }
}