<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Composer;

use Composer\Command\BaseCommand;
use Composer\Plugin\Capability\CommandProvider as CommandProviderCapability;
use Mediact\DependencyGuard\Composer\Command\CheckProductionDependencies;

class CommandProvider implements CommandProviderCapability
{
    /**
     * Retrieves a list of commands.
     *
     * @return BaseCommand[]
     */
    public function getCommands(): array
    {
        return [
            new CheckProductionDependencies()
        ];
    }
}
