<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Php\Filter;

class PatternSymbolFilter implements SymbolFilterInterface
{
    /** @var string */
    private $pattern;

    /**
     * Constructor.
     *
     * @param string $pattern
     */
    public function __construct(string $pattern)
    {
        $this->pattern = $pattern;
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
        return !fnmatch(
            $this->pattern,
            $symbol,
            FNM_PATHNAME | FNM_NOESCAPE
        );
    }
}
