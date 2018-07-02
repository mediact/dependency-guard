<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Php\Filter;

class ExactSymbolFilter implements SymbolFilterInterface
{
    /** @var string */
    private $search;

    /**
     * Constructor.
     *
     * @param string $search
     */
    public function __construct(string $search)
    {
        $this->search = $search;
    }

    /**
     * Filter the given symbol.
     *
     * @param string $symbol
     *
     * @return bool
     */
    public function __invoke(string $symbol): bool
    {
        return $symbol !== $this->search;
    }
}
