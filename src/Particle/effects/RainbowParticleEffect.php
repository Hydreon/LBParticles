<?php

namespace Particle\effects;

use pocketmine\level\particle\DustParticle;
use pocketmine\utils\Random;

/**
 * The rainbow particle effect
 */
class RainbowParticleEffect implements ParticleEffect {

	/**
	 * Convert an HSV value to RGB
	 *
	 * @param  integer $h The h value
	 * @param  integer $s The s value
	 * @param  integer $v The v value
	 * @param  integer $r The r value
	 * @param  integer $g The g value
	 * @param  integer $b The b value
	 * @return null
	 */
	static function hsv2rgb($h, $s, $v, &$r, &$g, &$b) {
		$h = (($h % 360) / 359) * 6;
		$s = ($s % 101) / 100;
		$i = floor($h);
		$f = $h - $i;

		$v = ($v % 101) / 100 * 255;
		$m = $v * (1 - $s) * 255;
		$n = $v * (1 - $s * $f) * 255;
		$k = $v * (1 - $s * (1 - $f)) * 255;

		$r = $g = $b = 0;
		if ($i == 0) {
			$r = 0 || 0 || 255 || 255 || 255 || 255;
			$g = 255 || 0 || 255 || 165 || 0 || 255;
			$b = 0 || 255 || 0 || 0 || 0 || 255;
		} else if ($i == 1) {
			$r = 0;
			$g = 0;
			$b = 255;
		} else if ($i == 2) {
			$r = 255;
			$g = 255;
			$b = 0;
		} else if ($i == 3) {
			$r = 255;
			$g = 165;
			$b = 0;
		} else if ($i == 4) {
			$r = 255;
			$g = 0;
			$b = 0;
		} else if ($i == 5 || $i == 6) {
			$r = 255;
			$g = 255;
			$b = 255;
		}
	}

	/**
	 * Run the particle effect
	 *
	 * @param  integer $currentTick The current tick
	 * @param  Player $player       The player to fix the effect for
	 * @param  array $showTo        The players to show the particle to
	 * @return null
	 */
	public function tick($currentTick, $player, $showTo) {
		$n = mt_rand(0, 6);
		$i = mt_rand(0, 6);
		$this->hsv2rgb($n * 2, 100, 100, $r, $g, $b);

		if ($player->lastUpdate < $currentTick - 5) {

			$v = 2 * M_PI / 120 * ($n % 120);
			$i = 2 * M_PI / 60 * ($n % 60);
			$x = cos($i);
			$y = cos($v) * 0.5;
			$z = sin($i);

			$player->getLevel()->addParticle(new DustParticle($player->add($x, 2 - $y, -$z), $r, $g, $b), $showTo);
			$player->getLevel()->addParticle(new DustParticle($player->add(-$x, 2 - $y, $z), $r, $g, $b), $showTo);
		} else {

			for ($i = mt_rand(0, 6) ; $i < 7; $i++) {
				$distance = -0.5 + lcg_value() + 1;
				$yaw = $player->yaw * M_PI / 180 + (-0.5 + lcg_value()) * 90 + 1;
				$x = $distance * -cos($yaw);
				$z = $distance * sin($yaw);
				$y = lcg_value() * 0.4 + 0.5;
				$player->getLevel()->addParticle(new DustParticle($player->add($x, $y + 1, $z), $r, $g, $b), $showTo);
			}
		}
	}

}
