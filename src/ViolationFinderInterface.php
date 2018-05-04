<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard;

use Composer\Composer;
use Mediact\DependencyGuard\Php\SymbolIteratorInterface;

interface ViolationFinderInterface
{
    /**
     * Find violations for the given Composer instance and symbols.
     *
     * @param Composer                $composer
     * @param SymbolIteratorInterface $symbols
     *
     * @return ViolationIteratorInterface
     */
    public function find(
        Composer $composer,
        SymbolIteratorInterface $symbols
    ): ViolationIteratorInterface;
}
