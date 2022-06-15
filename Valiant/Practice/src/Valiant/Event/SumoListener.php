<?php

declare(strict_types=1);

namespace Valiant\Event;

use pocketmine\event\Listener;
use Valiant\Core;

class SumoListener implements Listener {

    private $plugin;

    public function __construct(Core $plugin) {
        $this->plugin = $plugin;
    }

}
