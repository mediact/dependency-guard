<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\Prodep\Php;

interface SymbolContainerInterface
{
    /**
     * Get the symbols that are present in the container.
     *
     * @return iterable|string[]
     */
    public function getSymbols(): iterable;
}
