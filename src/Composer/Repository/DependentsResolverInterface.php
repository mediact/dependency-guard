<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Composer\Repository;

interface DependentsResolverInterface
{
    /**
     * Resolve dependents for a package.
     *
     * @param string $package
     *
     * @return array
     */
    public function resolve(string $package): array;
}
