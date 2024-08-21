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
 * @version 1.0.0
 *
 */

declare(strict_types=1);

namespace fly;

use fly\commands\FlyCommand;
use fly\commands\FlySpeedCommand;
use fly\listener\PlayerJoinListener;
use iriss\IrissCommand;
use nacre\NacreUI;
use olymp\PermissionManager;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use sofia\Updater;
use function file_exists;

class Main extends PluginBase {
	use SingletonTrait;

	protected function onLoad() : void {
		self::setInstance($this);
		$this->saveResource('config.yml', true);
	}

	protected function onEnable() : void {

		if (!file_exists($this->getFile() . 'vendor')) {
			$this->getLogger()->error('Merci d\'installer une release du plugin et non le code source. (https://github.com/Synopsie/Fly/releases)');
			$this->getServer()->getPluginManager()->disablePlugin($this);
			return;
		}

		require $this->getFile() . 'vendor/autoload.php';

		Updater::checkUpdate('Fly', $this->getDescription()->getVersion(), 'Synopsie', 'Fly');

		NacreUI::register($this);
		IrissCommand::register($this);

		$config = $this->getConfig();

		$this->getServer()->getPluginManager()->registerEvents(new PlayerJoinListener(), $this);

		$permission = new PermissionManager();
		$permission->registerPermission(
			$config->getNested('commands.fly.permission.name', 'synopsie.fly.use'),
			'Fly',
			$permission->getType($config->getNested('commands.fly.permission.default', 'operator'))
		);
		$permission->registerPermission(
			$config->getNested('commands.fly.other.permission.name', 'synopsie.fly.other'),
			'FlyOther',
			$permission->getType($config->getNested('commands.fly.other.permission.default', 'operator'))
		);
		$permission->registerPermission(
			$config->getNested('commands.flyspeed.permission.name', 'synopsie.flyspeed.use'),
			'FlySpeed',
			$permission->getType($config->getNested('commands.flyspeed.permission.default', 'operator'))
		);
		$permission->registerPermission(
			$config->getNested('commands.flyspeed.other.permission.name', 'synopsie.flyspeed.other'),
			'FlySpeedOther',
			$permission->getType($config->getNested('commands.flyspeed.other.permission.default', 'operator'))
		);

		$this->getServer()->getCommandMap()->registerAll(
			'Synopsie',
			[
				new FlyCommand(
					$config->getNested('commands.fly.name', 'fly'),
					$config->getNested('commands.fly.description', 'Enable fly mode'),
					$config->getNested('commands.fly.usage', '/fly [player] [time]'),
					[],
				),
				new FlySpeedCommand(
					$config->getNested('commands.flyspeed.name', 'flyspeed'),
					$config->getNested('commands.flyspeed.description', 'Change fly speed'),
					$config->getNested('commands.flyspeed.usage', '/flyspeed [player] [speed]'),
					[],
				)
			]
		);

	}
}
