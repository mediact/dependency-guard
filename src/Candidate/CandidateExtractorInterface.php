<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Candidate;

use Composer\Composer;
use Mediact\DependencyGuard\Php\SymbolIteratorInterface;

interface CandidateExtractorInterface
{
    /**
     * Extract violation candidates from the given Composer instance and symbols.
     *
     * @param Composer                $composer
     * @param SymbolIteratorInterface $symbols
     *
     * @return iterable|CandidateInterface[]
     */
    public function extract(
        Composer $composer,
        SymbolIteratorInterface $symbols
    ): iterable;
}
