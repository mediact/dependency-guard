<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Iterator;

use Composer\Composer;

interface FileIteratorFactoryInterface
{
    /**
     * Get an iterable list of source files for the root package of the given
     * Composer instance.
     *
     * @param Composer $composer
     *
     * @return FileIteratorInterface
     */
    public function create(Composer $composer): FileIteratorInterface;
}
