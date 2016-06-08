<?php

namespace Particle\effects;


interface ParticleEffect {
	public function tick($currentTick, $player, $showTo);
}
