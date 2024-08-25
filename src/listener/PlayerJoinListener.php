<?php

/*
 *  ____   __   __  _   _    ___    ____    ____    ___   _____
 * / ___|  \ \ / / | \ | |  / _ \  |  _ \  / ___|  |_ _| | ____|
 * \___ \   \ V /  |  \| | | | | | | |_) | \___ \   | |  |  _|
 *  ___) |   | |   | |\  | | |_| | |  __/   ___) |  | |  | |___
 * |____/    |_|   |_| \_|  \___/  |_|     |____/  |___| |_____|
 *
 * Ce plugin permet d'activer ou de désactiver le fly pendant une période définit ou non,
 * ainsi que de modifier ça vitesse de vole.
 *
 * @author Synopsie
 * @link https://github.com/Synopsie
 * @version 1.0.1
 *
 */

declare(strict_types=1);

namespace fly\listener;

use fly\Main;
use fly\session\Session;
use fly\task\FlyTask;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;

class PlayerJoinListener implements Listener {
	public function onPlayerJoin(PlayerJoinEvent $event) : void {
		$player  = $event->getPlayer();
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
