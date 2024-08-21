<?php
declare(strict_types=1);

namespace fly\listener;

use fly\Main;
use fly\session\Session;
use fly\task\FlyTask;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;

class PlayerJoinListener implements Listener {

    public function onPlayerJoin(PlayerJoinEvent $event) : void {
        $player = $event->getPlayer();
        $session = Session::get($player);
        if($session->getFlyTime() > 0) {
            Main::getInstance()->getScheduler()->scheduleRepeatingTask(new FlyTask($player, $session->getFlyTime()), 20);
            $player->setAllowFlight(true);
            $player->setFlying(true);
            if(Main::getInstance()->getConfig()->get('no.clip.in.fly')) {
                $player->setHasBlockCollision(false);
            }
        }
        if($session->getFlySpeed() > 0) {
            $session->setAbility($session->getFlySpeed());
        }
    }

}