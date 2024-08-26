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
use fly\task\FlyTask;
use fly\utils\Utils;
use iriss\CommandBase;
use iriss\parameters\IntParameter;
use iriss\parameters\PlayerParameter;
use pocketmine\command\CommandSender;
use pocketmine\lang\Translatable;
use pocketmine\player\Player;
use pocketmine\Server;
use function str_replace;

class FlyCommand extends CommandBase {
	public function __construct(string $name, string|Translatable $description, string $usageMessage, array $subCommands = [], array $aliases = []) {
		parent::__construct($name, $description, $usageMessage, $subCommands, $aliases);
		$this->setPermissions(
			[
				Main::getInstance()->getConfig()->getNested('commands.fly.permission.name', 'synopsie.fly.use'),
				Main::getInstance()->getConfig()->getNested('commands.fly.other.permission.name', 'synopsie.fly.other')
			]
		);
	}

	public function getCommandParameters() : array {
		return [
			new PlayerParameter('target', true),
			new IntParameter('time', false, true)
		];
	}

	protected function onRun(CommandSender $sender, array $parameters) : void {
		$config = Main::getInstance()->getConfig();
		if(!isset($parameters['target'])) {
			if($sender instanceof Player) {
				if(!$sender->isCreative()) {
					if(!$sender->getAllowFlight()) {
						$sender->setAllowFlight(true);
						$sender->setFlying(true);
						if($config->get('no.clip.in.fly')) {
							$sender->setHasBlockCollision(false);
						}
						$sender->sendMessage($config->get('fly.enabled', "Vous venez d'§aactiver §fle vole."));
					} else {
						$sender->setAllowFlight(false);
						$sender->setFlying(false);
						if($config->get('no.clip.in.fly')) {
							$sender->setHasBlockCollision(true);
						}
						$sender->sendMessage($config->get('fly.disabled', "Vous venez de §cdésactiver §fle vole."));
					}
				} else {
					$sender->sendMessage($config->get('use.command.increative', "§cVous ne pouvez pas utiliser cette commande en créatif."));
				}
			} else {
				$sender->sendMessage($config->get('use.command.ingame'));
			}
		} else {
			if(!$sender->hasPermission($this->getPermissions()[1])) {
				$sender->sendMessage($config->get('no.permission', "§cVous n'avez pas la permission d'utiliser cette commande."));
				return;
			}
			$target = Server::getInstance()->getPlayerExact($parameters['target']);
			if(!$target instanceof Player) {
				$sender->sendMessage($config->get('player.not.found', "§cLe joueur n'a pas été trouvé."));
				return;
			}
			if(!$target->isCreative()) {
				if(isset($parameters['time'])) {
					$session = Session::get($target);
					$session->setFlyTime($parameters['time']);
					$target->setAllowFlight(true);
					$target->setFlying(true);
					if($config->get('no.clip.in.fly')) {
						$target->setHasBlockCollision(false);
					}
					$target->sendMessage(str_replace(["%staff%", "%time%"], [$sender->getName(), Utils::convertTime($parameters['time'])], $config->get('fly.enabled.by.time', "§e%staff% §fvient de vous §aactiver §fle vole pour §e%time%§f.")));
					$sender->sendMessage(str_replace(["%player%", "%time%"], [$target->getName(), Utils::convertTime($parameters['time'])], $config->get('fly.enabled.to.time', "Vous venez de §aactiver §fle vole de §e%player% pour §e%time%§f.")));
					Main::getInstance()->getScheduler()->scheduleRepeatingTask(new FlyTask($target, $parameters['time']), 20);
				} else {
					if(!$target->getAllowFlight()) {
						$target->setAllowFlight(true);
						$target->setFlying(true);
						if($config->get('no.clip.in.fly')) {
							$target->setHasBlockCollision(false);
						}
						$target->sendMessage(str_replace("%staff%", $sender->getName(), $config->get('fly.enabled.by', "§e%staff% §fvient de vous §aactiver §fle vole.")));
						$sender->sendMessage(str_replace("%player%", $target->getName(), $config->get('fly.enabled.to', "Vous venez de §aactiver §fle vole de §e%player%.")));
					} else {
						$target->setAllowFlight(false);
						$target->setFlying(false);
						if($config->get('no.clip.in.fly')) {
							$target->setHasBlockCollision(true);
						}
						$target->sendMessage(str_replace("%staff%", $sender->getName(), $config->get('fly.disabled.by', "§e%staff% §fvient de vous §cdésactiver §fle vole.")));
						$sender->sendMessage(str_replace("%player%", $target->getName(), $config->get('fly.disabled.to', "Vous venez de §cdésactiver §fle vole de §e%player%.")));
					}
				}
			} else {
				$target->sendMessage($config->get('use.command.increative', "§cVous ne pouvez pas utiliser cette commande en créatif."));
			}
		}
	}

}
