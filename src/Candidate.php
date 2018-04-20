<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard;

use Composer\Package\PackageInterface;
use Mediact\DependencyGuard\Php\SymbolIteratorInterface;

class Candidate implements CandidateInterface
{
    /** @var PackageInterface */
    private $package;

    /** @var SymbolIteratorInterface */
    private $symbols;

    /**
     * Constructor.
     *
     * @param PackageInterface        $package
     * @param SymbolIteratorInterface $symbols
     */
    public function __construct(
        PackageInterface $package,
        SymbolIteratorInterface $symbols
    ) {
        $this->package = $package;
        $this->symbols = $symbols;
    }

    /**
     * Get the composer package.
     *
     * @return PackageInterface
     */
    public function getPackage(): PackageInterface
    {
        return $this->package;
    }

    /**
     * Get the symbols.
     *
     * @return SymbolIteratorInterface
     */
    public function getSymbols(): SymbolIteratorInterface
    {
        return $this->symbols;
    }
}
