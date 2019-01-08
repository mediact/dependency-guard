<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Composer\Locker;

use Composer\Package\Locker;
use Composer\Package\PackageInterface;
use SplObjectStorage;

class PackageRequirementsResolver implements PackageRequirementsResolverInterface
{
    /** @var SplObjectStorage|PackageInterface[][][] */
    private $resolvedLockers;

    /**
     * Constructor.
     *
     * @param SplObjectStorage|null $resolvedLockers
     */
    public function __construct(SplObjectStorage $resolvedLockers = null)
    {
        $this->resolvedLockers = $resolvedLockers ?? new SplObjectStorage();
    }

    /**
     * Resolve the dependents graph for the given Composer locker.
     *
     * @param Locker $locker
     *
     * @return array|PackageInterface[][]
     */
    public function resolve(Locker $locker): array
    {
        if (!$this->resolvedLockers->contains($locker)) {
            $lockedPackages = $locker->getLockedRepository()->getPackages();

            $this->resolvedLockers->attach(
                $locker,
                array_map(
                    function (array $packages) use ($lockedPackages) : array {
                        return array_reduce(
                            $lockedPackages,
                            function (
                                array $carry,
                                PackageInterface $package
                            ) use ($packages) : array {
                                if (in_array(
                                    $package->getName(),
                                    $packages,
                                    true
                                )) {
                                    $carry[] = $package;
                                }

                                return $carry;
                            },
                            []
                        );
                    },
                    $this->resolveGraph(
                        ...$locker->getLockData()['packages'] ?? []
                    )
                )
            );
        }

        return $this->resolvedLockers->offsetGet($locker);
    }

    /**
     * Get the dependent packages for the given package, using the given locker.
     *
     * @param string $package
     * @param Locker $locker
     *
     * @return array|PackageInterface[]
     */
    public function getDependents(string $package, Locker $locker): array
    {
        return $this->resolve($locker)[$package] ?? [];
    }

    /**
     * Resolve the dependents graph for the given packages.
     *
     * @param array ...$packages
     *
     * @return string[]
     */
    private function resolveGraph(array ...$packages): array
    {
        $graph = array_reduce(
            $packages,
            function (array $carry, array $package): array {
                foreach (array_keys($package['require'] ?? []) as $link) {
                    if (!preg_match('/^[^\/]+\/[^\/]+$/', $link)) {
                        // Most likely a platform requirement.
                        // E.g.: php
                        // E.g.: ext-openssl
                        continue;
                    }

                    if (!array_key_exists($link, $carry)) {
                        $carry[$link] = [];
                    }

                    if (!in_array($package['name'], $carry[$link], true)) {
                        $carry[$link][] = $package['name'];
                    }
                }

                return $carry;
            },
            []
        );

        // While the graph keeps receiving updates, keep on resolving.
        for ($previousGraph = []; $graph !== $previousGraph;) {
            // Do not update the previous graph before the current iteration
            // has started.
            $previousGraph = $graph;

            // For each package and its dependents in the graph ...
            foreach ($graph as $package => $dependents) {
                // ... Update the dependents of the package with grandparents.
                $graph[$package] = array_reduce(
                    // Determine grandparents by looking up the parents of the
                    // available dependents.
                    $dependents,
                    function (array $carry, string $parent) use ($graph): array {
                        foreach ($graph[$parent] ?? [] as $grandparent) {
                            if (!in_array($grandparent, $carry, true)) {
                                $carry[] = $grandparent;
                            }
                        }

                        return $carry;
                    },
                    // Start out with the current list of dependents.
                    $dependents
                );
            }
        }

        return $graph;
    }
}
