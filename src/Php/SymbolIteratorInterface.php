<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Php;

use Iterator;

interface SymbolIteratorInterface extends Iterator
{
    /**
     * Get the current symbol.
     *
     * @return SymbolInterface
     */
    public function current(): SymbolInterface;
}
