LBParticles - A simple plugin for adding particles to your server
===========

## This plugin allows a user to add particles to their server. The commands are simple:

| Command | Sub Command | User | Params | Description |
|:-------:|:-----------:|:----:|:------:|:-----------:|
|`particles`|`give`|`<name>`|`LavaParticleEffect, PortalParticleEffect, RainbowParticleEffect, RedstoneParticleEffect`| Spawns a player's particle |
|`particles`|`remove`|`<name>`|    | Removes a player's particles |

## The plugin can also be accessed by its API

```
$LBParticles = Server::getInstance()->getPluginManager()->getPlugin('LBParticles');

// Give a particle
$LBParticles->giveParticle($user, $particle);

// Remove a particle
$LBParticles->removeParticle($user);
```
