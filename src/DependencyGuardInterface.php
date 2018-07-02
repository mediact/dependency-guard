<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard;

use Composer\Composer;
use Mediact\DependencyGuard\Violation\ViolationIteratorInterface;

interface DependencyGuardInterface
{
    /**
     * Determine dependency violations for the given Composer instance.
     *
     * @param Composer $composer
     *
     * @return ViolationIteratorInterface
     */
    public function determineViolations(Composer $composer): ViolationIteratorInterface;
}
