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

namespace fly\commands;

use fly\Main;
use fly\session\Session;
use iriss\CommandBase;
use iriss\parameters\IntParameter;
use iriss\parameters\PlayerParameter;
use pocketmine\command\CommandSender;
use pocketmine\lang\Translatable;
use pocketmine\player\Player;
use pocketmine\Server;
use function str_replace;

class FlySpeedCommand extends CommandBase {
	public function __construct(string $name, string|Translatable $description, string $usageMessage, array $subCommands = [], array $aliases = []) {
		parent::__construct($name, $description, $usageMessage, $subCommands, $aliases);
		$this->setPermissions(
			[
				Main::getInstance()->getConfig()->getNested('commands.flyspeed.permission.name', 'synopsie.flyspeed.use'),
				Main::getInstance()->getConfig()->getNested('commands.flyspeed.other.permission.name', 'synopsie.flyspeed.other')
			]
		);
	}

	public function getCommandParameters() : array {
		return [
			new IntParameter('speed', false, false),
			new PlayerParameter('target', true)
		];
	}

	protected function onRun(CommandSender $sender, array $parameters) : void {
		$config = Main::getInstance()->getConfig();
		if(!isset($parameters['target'])) {
			if(!$sender instanceof Player) {
				$sender->sendMessage($config->get('use.command.ingame', 'Veuillez utiliser cette commande en jeu.'));
				return;
			}
			$player = $sender;
		} else {
			$player = Server::getInstance()->getPlayerExact($parameters['target']);
		}
		if($player === null) {
			$sender->sendMessage($config->get('player.not.found', "§cLe joueur n'a pas été trouvé."));
			return;
		}
		$session = Session::get($player);
		$session->setFlySpeed($parameters['speed']);
		$session->setAbility($parameters['speed']);
		$player->sendMessage(str_replace("%speed%", (string) $parameters['speed'], $config->get('flyspeed.set', 'Vous avez défini votre vitesse de vole sur §e%speed%§f.')));
		if (isset($parameters['target'])) {
			$sender->sendMessage(str_replace(['%speed%', '%player%'], [(string) $parameters['speed'], $player->getName()], $config->get('flyspeed.set.to', "Vous avez défini la vitesse de vole de §e%player% §fsur §e%speed%§f.")));
		}
	}

}
