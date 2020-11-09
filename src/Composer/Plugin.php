<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Composer;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\Capability\CommandProvider as CommandProviderCapability;
use Composer\Plugin\Capable;
use Composer\Plugin\PluginInterface;

class Plugin implements PluginInterface, Capable
{
    /**
     * Announce the capabilities of the dependency guard plugin.
     *
     * @return string[]
     */
    public function getCapabilities(): array
    {
        return [
            CommandProviderCapability::class => CommandProvider::class
        ];
    }

    /**
     * Apply plugin modifications to Composer.
     *
     * @param Composer    $composer
     * @param IOInterface $io
     *
     * @return void
     */
    public function activate(Composer $composer, IOInterface $io): void
    {
        // There is nothing explicit that needs to happen.
    }
    
    /**
     * Remove any hooks from Composer
     *
     * This will be called when a plugin is deactivated before being
     * uninstalled, but also before it gets upgraded to a new version
     * so the old one can be deactivated and the new one activated.
     *
     * @param Composer    $composer
     * @param IOInterface $io
     */
    public function deactivate(Composer $composer, IOInterface $io): void
    {
        // There is nothing explicit that needs to happen.
    }

    /**
     * Prepare the plugin to be uninstalled
     *
     * This will be called after deactivate.
     *
     * @param Composer    $composer
     * @param IOInterface $io
     */
    public function uninstall(Composer $composer, IOInterface $io): void
    {
        // There is nothing explicit that needs to happen.
    }
}
