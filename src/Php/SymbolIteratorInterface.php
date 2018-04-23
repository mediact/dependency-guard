<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Php;

use Iterator;
use JsonSerializable;

interface SymbolIteratorInterface extends Iterator, JsonSerializable
{
    /**
     * Get the current symbol.
     *
     * @return SymbolInterface
     */
    public function current(): SymbolInterface;
}
