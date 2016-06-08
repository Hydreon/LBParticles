<?php

namespace Particle\effects;

use pocketmine\level\particle\LavaDripParticle;
use pocketmine\level\particle\LavaParticle;


class LavaParticleEffect implements ParticleEffect {

	public function tick($currentTick, $player, $showTo) {
		$player->getLevel()->addParticle(new LavaParticle($player->add(0, 1 + lcg_value(), 0)), $showTo);

		$distance = -0.5 + lcg_value();
		$yaw = $player->yaw * M_PI / 180;
		$x = $distance * cos($yaw);
		$z = $distance * sin($yaw);
		$player->getLevel()->addParticle(new LavaDripParticle($player->add($x, 0.2, $z)), $showTo);
	}

}
