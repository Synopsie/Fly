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
 * @version 1.0.2
 *
 */

declare(strict_types=1);

namespace fly\session;

use pocketmine\network\mcpe\NetworkSession;
use pocketmine\player\Player;
use WeakMap;

trait SessionStorage {
	private Player $player;
	private static WeakMap $map;
	protected static ?array $tradeInfos = null;
	protected static bool $tradeMode    = false;

	public static function get(Player $player) : Session {
		if(!isset(self::$map[$player])) {
			/** @var WeakMap<Player, Session> $weakMap */
			$weakMap   = new WeakMap();
			self::$map = $weakMap;
		}
		return self::$map[$player] ?? new Session($player->getNetworkSession());
	}

	public function __construct(NetworkSession $session) {
		$this->player = $session->getPlayer();
	}

	public static function create(NetworkSession $session) : Session {
		return new Session($session);

	}

	public function getPlayer() : Player {
		return $this->player;
	}

}
