<?php

namespace Particle;

use Particle\effects\LavaParticleEffect;
use Particle\effects\ParticleEffect;
use Particle\effects\PortalParticleEffect;
use Particle\effects\RainbowParticleEffect;
use Particle\effects\RedstoneParticleEffect;

class ParticleManager {

	public static $lava;

	public static $redstone;

	public static $portal;

	public static $rainbow;

	private $plugin;

	private $task;

	public static function initParticleEffects() {
		self::$lava = new LavaParticleEffect();
		self::$redstone = new RedstoneParticleEffect();
		self::$portal = new PortalParticleEffect();
		self::$rainbow = new RainbowParticleEffect();
	}

	public function __construct($plugin) {
		self::initParticleEffects();
		$this->plugin = $plugin;
		$this->task = new ParticleTask($this->plugin);
		$this->plugin->getServer()->getScheduler()->scheduleRepeatingTask($this->task, 3);
	}

	public function setPlayerParticleEffect($player, ParticleEffect $effect) {
		$this->task->setPlayerParticleEffect($player, $effect);

		return $effect;
	}

	public function removeEffect($player) {
		$this->task->removeEffect($player);
	}

}
