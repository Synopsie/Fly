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

namespace fly\utils;

use function intdiv;
use function trim;

class Utils {
	public static function convertTime(int $time) : string {
		$hours   = intdiv($time, 3600);
		$minutes = intdiv($time % 3600, 60);
		$seconds = $time % 60;

		$result = '';
		if ($hours > 0) {
			$result .= $hours . 'h ';
		}
		if ($minutes > 0) {
			$result .= $minutes . 'm ';
		}
		if ($seconds > 0 || ($hours === 0 && $minutes === 0)) {
			$result .= $seconds . 's';
		}

		return trim($result);
	}

}
