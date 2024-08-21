<?php
declare(strict_types=1);

namespace fly\task;

use fly\Main;
use fly\session\Session;
use fly\utils\Utils;
use nacre\bossbar\BossBar;
use nacre\bossbar\BossBarColor;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;

class FlyTask extends Task {

    private int $initTime;
    private BossBar $bossbar;
    private Session $session;

    public function __construct(private readonly Player $player, private int $time, private ?int $baseTime = null) {
        $this->initTime = $this->baseTime ?? $this->time;
        $this->bossbar = new BossBar();
        $color = match (strtolower(Main::getInstance()->getConfig()->get('bossbar.color', 'red'))) {
            'yellow' => BossBarColor::YELLOW,
            'green' => BossBarColor::GREEN,
            'blue' => BossBarColor::BLUE,
            'purple' => BossBarColor::PURPLE,
            'white' => BossBarColor::WHITE,
            'pink' => BossBarColor::PINK,
            default => BossBarColor::RED
        };
        $this->bossbar->setColor($color);
        $this->bossbar->addPlayer($this->player);
        $this->session = Session::get($this->player);
    }

    public function onRun() : void {
        $bossbar = $this->bossbar;
        if(!$this->player->isOnline()) {
            $bossbar->removePlayer($this->player);
            $this->session->setFlyTime($this->time);
            $this->getHandler()->cancel();
            return;
        }
        $config = Main::getInstance()->getConfig();
        $bossbar->setTitle(str_replace('%time%', Utils::convertTime($this->time), $config->get('bossbar.title', 'Fly mode: %time%')));
        $bossbar->setPercentage($this->time / $this->initTime);
        if($config->get('use.sound')){
            if($this->time <= 5) {
                $position = $this->player->getPosition();
                $packet = PlaySoundPacket::create(
                    'note.bass',
                    $position->x,
                    $position->y,
                    $position->z,
                    100,
                    1
                );
                $this->player->getNetworkSession()->sendDataPacket($packet);
            }
        }
        if($this->time <= 0) {
            $this->player->setAllowFlight(false);
            $this->player->setFlying(false);
            if($config->get('no.clip.in.fly')) {
                $this->player->setHasBlockCollision(true);
            }
            $this->player->sendMessage($config->get('fly.disabled', "Vous venez de §cdésactiver §fle vole."));
            $bossbar->removePlayer($this->player);
            $this->getHandler()->cancel();
        }
        $this->time--;
    }

}