<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Composer\Repository;

/**
 * @deprecated The conceptual difference between dependents in Composer and
 *   DependencyGuard is too great to rely on the output of a dependents resolver.
 */
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
