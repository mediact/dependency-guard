<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Php\Filter;

class SymbolFilterChain implements SymbolFilterInterface
{
    /** @var SymbolFilterInterface[] */
    private $filters;

    /**
     * Constructor.
     *
     * @param SymbolFilterInterface[] ...$filters
     */
    public function __construct(SymbolFilterInterface ...$filters)
    {
        $this->filters = $filters;
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
        return array_reduce(
            $this->filters,
            function (
                bool $carry,
                SymbolFilterInterface $filter
            ) use (
                $symbol
            ) : bool {
                return $carry && $filter($symbol);
            },
            true
        );
    }
}
