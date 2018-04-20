<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Php;

use PhpParser\Node\Name;
use PhpParser\NodeVisitor;

interface SymbolTrackerInterface extends NodeVisitor
{
    /**
     * Get the symbols that are present in the container.
     *
     * @return iterable|Name[]
     */
    public function getSymbols(): iterable;
}
