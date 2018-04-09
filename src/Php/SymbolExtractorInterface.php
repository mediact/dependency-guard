<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\Prodep\Php;

use Mediact\Prodep\Iterator\FileIteratorInterface;

interface SymbolExtractorInterface
{
    /**
     * Extract the PHP symbols from the given files.
     *
     * @param FileIteratorInterface $files
     * @param string[]              ...$exclusions
     *
     * @return SymbolContainerInterface
     */
    public function extract(
        FileIteratorInterface $files,
        string ...$exclusions
    ): SymbolContainerInterface;
}
