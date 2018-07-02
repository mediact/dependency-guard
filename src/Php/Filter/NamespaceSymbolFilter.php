<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Php\Filter;

class NamespaceSymbolFilter implements SymbolFilterInterface
{
    /** @var string */
    private $namespace;

    /**
     * Constructor.
     *
     * @param string $namespace
     */
    public function __construct(string $namespace)
    {
        $this->namespace = $namespace;
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
        return strpos($symbol, $this->namespace) !== 0;
    }
}
