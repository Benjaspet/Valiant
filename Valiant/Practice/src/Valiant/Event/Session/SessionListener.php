<?php

declare(strict_types=1);

namespace Valiant\Event\Session;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;

class SessionListener implements Listener {

    private $manager;

    public function __construct(SessionManager $manager) {
        $this->manager = $manager;
    }

    public function onLogin(PlayerLoginEvent $event): void {
        $this->manager->openSession($event->getPlayer());
    }

    public function onQuit(PlayerQuitEvent $event): void {
        $this->manager->closeSession($event->getPlayer());
    }
}
