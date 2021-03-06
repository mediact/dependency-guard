<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Php;

use ArrayIterator;
use IteratorIterator;

class SymbolIterator extends IteratorIterator implements SymbolIteratorInterface
{
    /**
     * Constructor.
     *
     * @param SymbolInterface ...$symbols
     */
    public function __construct(SymbolInterface ...$symbols)
    {
        parent::__construct(
            new ArrayIterator($symbols)
        );
    }

    /**
     * Get the current symbol.
     *
     * @return SymbolInterface
     */
    public function current(): SymbolInterface
    {
        return parent::current();
    }

    /**
     * Specify data that should be serialized to JSON.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return iterator_to_array($this);
    }
}
