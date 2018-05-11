<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Php;

use Mediact\DependencyGuard\Iterator\FileIteratorInterface;
use Mediact\DependencyGuard\Php\Filter\SymbolFilterInterface;

interface SymbolExtractorInterface
{
    /**
     * Extract the PHP symbols from the given files.
     *
     * @param FileIteratorInterface $files
     * @param SymbolFilterInterface $filter
     *
     * @return SymbolIteratorInterface
     */
    public function extract(
        FileIteratorInterface $files,
        SymbolFilterInterface $filter
    ): SymbolIteratorInterface;
}
