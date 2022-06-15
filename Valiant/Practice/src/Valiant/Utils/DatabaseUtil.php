<?php

declare(strict_types=1);

namespace Valiant\Utils;

use pocketmine\command\CommandSender;
use pocketmine\entity\Skin;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\utils\Utils;
use Valiant\Core;
use Valiant\Libs\CustomForm;
use Valiant\Libs\SimpleForm;

class DatabaseUtil {

    private $plugin;
    public $db;

    public function __construct(Core $plugin) {
        $this->plugin = $plugin;
    }
}