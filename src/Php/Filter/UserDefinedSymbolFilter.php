<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Php\Filter;

use ReflectionClass;
use Throwable;

class UserDefinedSymbolFilter implements SymbolFilterInterface
{
    /**
     * Filter the given symbol.
     *
     * @param string $symbol
     *
     * @return bool
     */
    public function __invoke(string $symbol): bool
    {
        if (!class_exists($symbol)) {
            return false;
        }

        try {
            $reflection = new ReflectionClass($symbol);
        } catch (Throwable $e) {
            return false;
        }

        return !$reflection->isInternal();
    }
}
