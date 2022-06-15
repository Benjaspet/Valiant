<?php

declare(strict_types=1);

namespace Valiant\Utils\Player;

use pocketmine\command\CommandSender;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\level\Level;
use pocketmine\level\particle\ExplodeParticle;
use pocketmine\level\particle\HugeExplodeParticle;
use pocketmine\level\Position;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\Player;
use pocketmine\utils\Config;
use Valiant\Core;
use Valiant\Libs\CustomForm;
use Valiant\Task\Async\ProxyTask;

class PlayerUtil {

    private $plugin;

    public $taggedPlayer = [];

    public function __construct(Core $plugin) {
        $this->plugin = $plugin;
    }

    public function setTagged(Player $player, $value = true) {
        if ($value) {
            $this->taggedPlayer[$player->getName()] = 16; // always one second higher than you want
        } else {
            unset($this->taggedPlayer[$player->getName()]);
        }
    }

    public function isTagged(Player $player): bool {
        return isset($this->taggedPlayer[$player->getName()]);
    }

    public function getTagDuration(Player $player): int {
        return ($this->isTagged($player) ? $this->taggedPlayer[$player->getName()] : 0);
    }
}