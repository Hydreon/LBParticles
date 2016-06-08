<?php

namespace Particle;

use pocketmine\scheduler\PluginTask;
use Particle\effects\ParticleEffect;


class ParticleTask extends PluginTask {

	private $plugin;
	private $effects = [];

	public function __construct($plugin) {
		parent::__construct($plugin);
		$this->plugin = $plugin;
	}

	public function setPlayerParticleEffect($player, ParticleEffect $effect) {
		$this->effects[$player->getId()] = [$player, $effect];
	}

	public function onRun($currentTick) {
		foreach ($this->effects as $id => $data) {

			$player = $data[0];
			$effect = $data[1];

			if ($player->closed) {
				unset($this->effects[$id]);
				continue;
			}

			$showTo = $player->getViewers();
			$showTo[] = $player;
			$effect->tick($currentTick, $player, $showTo);
		}
	}

	public function removeEffect($player) {
		unset($this->effects[$player->getId()]);
	}
}
