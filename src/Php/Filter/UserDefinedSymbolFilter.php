<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Php\Filter;

use Mediact\DependencyGuard\Reflection\ReflectionTrait;
use Throwable;

class UserDefinedSymbolFilter implements SymbolFilterInterface
{
    use ReflectionTrait;

    /**
     * Filter the given symbol.
     *
     * @param string $symbol
     *
     * @return bool
     */
    public function __invoke(string $symbol): bool
    {
        try {
            $reflection = $this->getClassReflection($symbol);
        } catch (Throwable $e) {
            return false;
        }

        return !$reflection->isInternal();
    }
}
