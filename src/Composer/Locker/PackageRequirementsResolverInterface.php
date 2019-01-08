<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Composer\Locker;

use Composer\Package\Locker;
use Composer\Package\PackageInterface;

interface PackageRequirementsResolverInterface
{
    /**
     * Resolve the dependents graph for the given Composer locker.
     *
     * @param Locker $locker
     *
     * @return array|PackageInterface[][]
     */
    public function resolve(Locker $locker): array;

    /**
     * Get the dependent packages for the given package, using the given locker.
     *
     * @param string $package
     * @param Locker $locker
     *
     * @return array|PackageInterface[]
     */
    public function getDependents(string $package, Locker $locker): array;
}
