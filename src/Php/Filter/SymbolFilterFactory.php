<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Php\Filter;

use Composer\Composer;

class SymbolFilterFactory implements SymbolFilterFactoryInterface
{
    /**
     * Create a symbol filter for the given Composer instance.
     *
     * @param Composer $composer
     *
     * @return SymbolFilterInterface
     */
    public function create(Composer $composer): SymbolFilterInterface
    {
        /** @var SymbolFilterInterface[] $filters */
        $filters = array_map(
            function (string $exclusion) : SymbolFilterInterface {
                $filters = [
                    new ExactSymbolFilter($exclusion),
                    new PatternSymbolFilter($exclusion)
                ];

                if (preg_match('#\\\\$#', $exclusion)) {
                    $filters[] = new NamespaceSymbolFilter($exclusion);
                }

                return new SymbolFilterChain(...$filters);
            },
            $this->getExclusions($composer)
        );

        $filters[] = new UserDefinedSymbolFilter();

        return new SymbolFilterChain(...$filters);
    }

    /**
     * Get the exclusions from the Composer root package.
     *
     * @param Composer $composer
     *
     * @return string[]
     */
    private function getExclusions(Composer $composer): array
    {
        $extra = $composer->getPackage()->getExtra();

        return $extra['dependency-guard']['exclude'] ?? [];
    }
}
