<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Php\Filter;

use Composer\Composer;

interface SymbolFilterFactoryInterface
{
    /**
     * Create a symbol filter for the given Composer instance.
     *
     * @param Composer $composer
     *
     * @return SymbolFilterInterface
     */
    public function create(Composer $composer): SymbolFilterInterface;
}
