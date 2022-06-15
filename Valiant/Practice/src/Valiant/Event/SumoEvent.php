<?php

declare(strict_types=1);

namespace Valiant\Event;

use Valiant\Core;

class SumoEvent {

    private $plugin;

    public function __construct(Core $plugin) {
        $this->plugin = $plugin;
    }
}
