<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Violation\Filter;

use Composer\Composer;

class ViolationFilterFactory implements ViolationFilterFactoryInterface
{
    /**
     * Create a violation filter for the given Composer instance.
     *
     * @param Composer $composer
     *
     * @return ViolationFilterInterface
     */
    public function create(Composer $composer): ViolationFilterInterface
    {
        return new ViolationFilterChain(
            ...array_merge(
                $this->getSuggestsFilters($composer),
                $this->getIgnoreFilters($composer)
            )
        );
    }

    /**
     * Exclude packages suggested by the root package from violating dependencies.
     *
     * @param Composer $composer
     *
     * @return ViolationFilterInterface[]
     */
    private function getSuggestsFilters(Composer $composer): array
    {
        return array_map(
            function (string $package) : ViolationFilterInterface {
                return new ExactPackageFilter($package);
            },
            array_keys(
                $composer->getPackage()->getSuggests()
            )
        );
    }

    /**
     * Get the violation filters for the ignore rules in the given Composer instance.
     *
     * @param Composer $composer
     *
     * @return ViolationFilterInterface[]
     */
    private function getIgnoreFilters(Composer $composer): array
    {
        $extra = $composer->getPackage()->getExtra();

        return array_map(
            function (string $rule) : ViolationFilterInterface {
                $filters = [
                    new ExactPackageFilter($rule),
                    new PatternPackageFilter($rule)
                ];

                if (preg_match('#/$#', $rule)) {
                    $filters[] = new VendorFilter($rule);
                }

                return new ViolationFilterChain(...$filters);
            },
            $extra['dependency-guard']['ignore'] ?? []
        );
    }
}
