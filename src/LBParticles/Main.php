<?php

namespace LBParticles;

use Particle\ParticleManager;
use pocketmine\Player;
use pocketmine\event\Listener;
use pocketmine\command\Command;
use pocketmine\utils\TextFormat;
use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandSender;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerRespawnEvent;

/**
 * The main class for the particles plugin
 */
class Main extends PluginBase implements Listener {

    /**
     * An array of players mapped to the type of particle effect
     *
     * @type array
     */
    public $players = [];

    /**
     * An array of particles mapped to a player
     *
     * @type array
     */
    public $particle = [];

    /**
     * Loads the plugin
     *
     * @return null
     */
    public function onLoad() {
        $this->getLogger()->info(TextFormat::WHITE . "Loaded");
    }

    /**
     * Enables the plugin
     *
     * @return null
     */
    public function onEnable() {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->saveDefaultConfig();
        $this->reloadConfig();

        /**
         * Disable the plugin if it's disabled in the plugin
         */
        if($this->getConfig()->get('particles') == false) {
            $this->setEnabled(false);
            return;
        }

        /**
         * Initalize the ParticleManager
         * @type ParticleManager
         */
        $this->manager = new ParticleManager($this);

        /**
         * Load users that have a particle effect from the config
         */
        foreach($this->getConfig()->get('users') as $user => $effect) {
            $this->players[$user] = $effect;
        }

        $this->getLogger()->info(TextFormat::DARK_GREEN . "Enabled");
    }

    /**
     * Handles the commands sent to the plugin
     *
     * @param  CommandSender $sender  The person issuing the command
     * @param  Command       $command The command object
     * @param  string        $label   The command label
     * @param  array         $args    An array of arguments
     * @return boolean                True allows the command to go through, false sends an error
     */
    public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
        $subcommand = strtolower(array_shift($args));
        switch ($subcommand) {
            case "give";
                if(count($args) < 1){
                    array_unshift($args, $sender->getDisplayName());
                }

                /**
                 * Check perms, then give particles
                 */
                if (!$this->getConfig()->get('onlyOp') || $sender->hasPermission("lbparticles")) {
                    if($this->giveParticle(...$args)) {
                        $sender->sendMessage(TextFormat::BLUE . '[LBParticles] ' . $args[0] . ' has a new particle effect!');
                    } else {
                        $this->getServer()->broadcastMessage(TextFormat::BLUE . '[LBParticles] Unable to give ' . $args[0] . ' a new particle effect!');
                    }
                    return true;
                }

                $sender->sendMessage(TextFormat::RED . "[LBParticles] You don't have permissions to do that...");
                return true;
            case "remove":
                if(count($args) < 1){
                    array_unshift($args, $sender->getDisplayName());
                }

                /**
                 * Check perms, then remove particles
                 */
                if (!$this->getConfig()->get('onlyOp') || $sender->hasPermission("lbparticles")) {
                    $args[] = true;
                    if($this->removeParticle(...$args)) {
                        $sender->sendMessage(TextFormat::RED . '[LBParticles] ' . $args[0] . '\'s particle effect was removed!');
                    } else {
                        $sender->sendMessage(TextFormat::RED . '[LBParticles] Unable to remove ' . $args[0] . '\'s particle effect!');
                    }
                    return true;
                }

                $sender->sendMessage(TextFormat::RED . "[LBParticles] You don't have permissions to do that...");
                return true;
            case "help":
                $sender->sendMessage(TextFormat::GREEN . '[LBParticles] Available commands: give, remove');
                return true;
                break;
            default:
                return false;
        }
    }

    /**
     * Give a player particles if they are in the config
     *
     * @param PlayerLoginEvent $event The login event
     */
    public function PlayerLoginEvent(PlayerLoginEvent $event) {
        if (isset($this->players[$event->getPlayer()->getDisplayName()])) {
            $this->giveParticle($event->getPlayer()->getDisplayName(), $this->players[$event->getPlayer()->getDisplayName()]);
        }
    }

    /**
     * Remove the particles from a player when they leave
     *
     * @param PlayerQuitEvent $event The quit event
     */
    public function PlayerQuitEvent(PlayerQuitEvent $event) {
        if (isset($this->players[$event->getPlayer()->getDisplayName()])) {
            $this->removeParticle($event->getPlayer()->getDisplayName());
        }
    }

    /**
     * Give a player particles when they respawn
     *
     * @param PlayerRespawnEvent $event The respawn event
     */
    public function PlayerRespawnEvent(PlayerRespawnEvent $event) {
        if (isset($this->players[$event->getPlayer()->getDisplayName()])) {
            $this->giveParticle($event->getPlayer()->getDisplayName(), $this->players[$event->getPlayer()->getDisplayName()]);
        }
    }

    /**
     * Give a user particles
     *
     * @param  string $user     The username of the person to give particles
     * @param  string $particle The particle effect to give (The class name)
     * @return boolean          Whether or not giving the particles was successful
     */
    public function giveParticle($user = '', $particle = '') {
        if(($player = $this->getServer()->getPlayerExact($user)) instanceof Player) {
            if(!isset($this->particles[$player->getDisplayName()])) {
                $name = $this->getParticleClass($particle);
                $this->particles[$player->getDisplayName()] = $this->manager->setPlayerParticleEffect($player, $this->manager::$$name);
                $this->players[$player->getDisplayName()] = get_class($this->particles[$player->getDisplayName()]);
                return true;
            }
        }
        return false;
    }

    /**
     * Remove the particles from the user
     *
     * @param  string $user  The username of the person to take the particles from
     * @param  boolean $unset Whether or not to unset the user from the config
     * @return boolean        Whether or not the command was successful
     */
    public function removeParticle($user = '', $unset = false) {
        if(($player = $this->getServer()->getPlayerExact($user)) instanceof Player) {
            if(isset($this->particles[$player->getDisplayName()])) {
                unset($this->particles[$player->getDisplayName()]);
                $this->manager->removeEffect($player);
                if($unset) {
                    unset($this->players[$player->getDisplayName()]);
                }
                return true;
            }
        }
        return false;
    }

    /**
     * Get the particle class for the manager
     * @param  string $particle The particle class
     * @return string           The particle
     */
    public function getParticleClass($particle) {
        $path = explode('\\', $particle);
        $particle = array_pop($path);
        switch($particle) {
            case 'LavaParticleEffect':
                $var = 'lava';
                break;
            case 'PortalParticleEffect':
                $var = 'portal';
                break;
            case 'RainbowParticleEffect':
                $var = 'rainbow';
                break;
            case 'RedstoneParticleEffect':
                $var = 'redstone';
                break;
            default:
                $var = 'portal';
                break;
        }
        return $var;
    }

    /**
     * Disables the plguin
     *
     * @return null
     */
    public function onDisable() {
        $this->getConfig()->set('users', $this->players);
        $this->getConfig()->save();

        $this->getLogger()->info(TextFormat::DARK_RED . "Disabled");
    }
}
