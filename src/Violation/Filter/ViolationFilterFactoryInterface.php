<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Violation\Filter;

use Composer\Composer;

interface ViolationFilterFactoryInterface
{
    /**
     * Create a violation filter for the given Composer instance.
     *
     * @param Composer $composer
     *
     * @return ViolationFilterInterface
     */
    public function create(Composer $composer): ViolationFilterInterface;
}
