<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard;

use Composer\Package\PackageInterface;
use Mediact\DependencyGuard\Php\SymbolIteratorInterface;

interface CandidateInterface
{
    /**
     * Get the composer package.
     *
     * @return PackageInterface
     */
    public function getPackage(): PackageInterface;

    /**
     * Get the symbols.
     *
     * @return SymbolIteratorInterface
     */
    public function getSymbols(): SymbolIteratorInterface;
}
