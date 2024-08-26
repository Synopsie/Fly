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

use fly\Main;
use pocketmine\network\mcpe\protocol\types\AbilitiesData;
use pocketmine\network\mcpe\protocol\types\AbilitiesLayer;
use pocketmine\network\mcpe\protocol\types\command\CommandPermissions;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\network\mcpe\protocol\UpdateAbilitiesPacket;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\permission\DefaultPermissions;
use pocketmine\utils\Config;

class Session {
	use SessionStorage;

	public function setFlyTime(int $time) : void {
		$config = new Config(Main::getInstance()->getDataFolder() . 'times.json', Config::JSON);
		$array  = [
			"time"      => $time,
			"base_time" => $time
		];
		$config->set($this->player->getName(), $array);
		$config->save();
	}

	public function getFlyTime() : int {
		$config = new Config(Main::getInstance()->getDataFolder() . 'times.json', Config::JSON);
		return $config->get($this->player->getName(), ['time' => 0, 'base_time' => 0])["time"];
	}

	public function getBaseFlyTime() : int {
		$config = new Config(Main::getInstance()->getDataFolder() . 'times.json', Config::JSON);
		return $config->get($this->player->getName(), 0)["base_time"];
	}

	public function setFlySpeed(int $speed) : void {
		$config = new Config(Main::getInstance()->getDataFolder() . 'speeds.json', Config::JSON);
		$config->set($this->player->getName(), $speed);
		$config->save();
	}

	public function getFlySpeed() : int {
		$config = new Config(Main::getInstance()->getDataFolder() . 'speeds.json', Config::JSON);
		return $config->get($this->player->getName(), 0);
	}
	public function setAbility(int $getFlySpeed) : void {
		$isOp = $this->getPlayer()->hasPermission(DefaultPermissions::ROOT_OPERATOR);

		$boolAbilities = [
			AbilitiesLayer::ABILITY_ALLOW_FLIGHT       => $this->getPlayer()->getAllowFlight(),
			AbilitiesLayer::ABILITY_FLYING             => $this->getPlayer()->isFlying(),
			AbilitiesLayer::ABILITY_NO_CLIP            => !$this->getPlayer()->hasBlockCollision(),
			AbilitiesLayer::ABILITY_OPERATOR           => $isOp,
			AbilitiesLayer::ABILITY_TELEPORT           => $this->getPlayer()->hasPermission(DefaultPermissionNames::COMMAND_TELEPORT_SELF),
			AbilitiesLayer::ABILITY_INVULNERABLE       => $this->getPlayer()->isCreative(),
			AbilitiesLayer::ABILITY_MUTED              => false,
			AbilitiesLayer::ABILITY_WORLD_BUILDER      => false,
			AbilitiesLayer::ABILITY_INFINITE_RESOURCES => !$this->getPlayer()->hasFiniteResources(),
			AbilitiesLayer::ABILITY_LIGHTNING          => false,
			AbilitiesLayer::ABILITY_BUILD              => !$this->getPlayer()->isSpectator(),
			AbilitiesLayer::ABILITY_MINE               => !$this->getPlayer()->isSpectator(),
			AbilitiesLayer::ABILITY_DOORS_AND_SWITCHES => !$this->getPlayer()->isSpectator(),
			AbilitiesLayer::ABILITY_OPEN_CONTAINERS    => !$this->getPlayer()->isSpectator(),
			AbilitiesLayer::ABILITY_ATTACK_PLAYERS     => !$this->getPlayer()->isSpectator(),
			AbilitiesLayer::ABILITY_ATTACK_MOBS        => !$this->getPlayer()->isSpectator(),
			AbilitiesLayer::ABILITY_PRIVILEGED_BUILDER => false,
		];

		$layers = [
			new AbilitiesLayer(AbilitiesLayer::LAYER_BASE, $boolAbilities, $getFlySpeed / 10, 0.1),
		];
		if(!$this->getPlayer()->hasBlockCollision()) {

			$layers[] = new AbilitiesLayer(AbilitiesLayer::LAYER_SPECTATOR, [
				AbilitiesLayer::ABILITY_FLYING => true,
			], null, null);
		}

		$this->getPlayer()->getNetworkSession()->sendDataPacket(UpdateAbilitiesPacket::create(new AbilitiesData(
			$isOp ? CommandPermissions::OPERATOR : CommandPermissions::NORMAL,
			$isOp ? PlayerPermissions::OPERATOR : PlayerPermissions::MEMBER,
			$this->getPlayer()->getId(),
			$layers
		)));
	}

}
