<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Php;

use Mediact\DependencyGuard\Iterator\FileIteratorInterface;

interface SymbolExtractorInterface
{
    /**
     * Extract the PHP symbols from the given files.
     *
     * @param FileIteratorInterface $files
     * @param string[]              ...$exclusions
     *
     * @return SymbolIteratorInterface
     */
    public function extract(
        FileIteratorInterface $files,
        string ...$exclusions
    ): SymbolIteratorInterface;
}
