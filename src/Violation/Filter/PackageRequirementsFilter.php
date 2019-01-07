<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Violation\Filter;

use Composer\Package\Locker;
use Composer\Package\Package;
use Mediact\DependencyGuard\Candidate\Candidate;
use Mediact\DependencyGuard\Violation\Violation;
use Mediact\DependencyGuard\Violation\ViolationInterface;

class PackageRequirementsFilter implements ViolationFilterInterface
{
    /** @var Locker */
    private $locker;

    /** @var ViolationFilterInterface */
    private $filter;

    /** @var array|null */
    private $graph;

    /**
     * Constructor.
     *
     * @param Locker                   $locker
     * @param ViolationFilterInterface $filter
     */
    public function __construct(
        Locker $locker,
        ViolationFilterInterface $filter
    ) {
        $this->locker = $locker;
        $this->filter = $filter;
    }

    /**
     * Filter violations.
     *
     * @param ViolationInterface $violation
     *
     * @return bool
     */
    public function __invoke(ViolationInterface $violation): bool
    {
        return array_reduce(
            array_map(
                function (
                    string $dependent
                ) use ($violation): ViolationInterface {
                    $package = $this
                        ->locker
                        ->getLockedRepository()
                        ->findPackage($dependent, '*')
                        ?? new Package($dependent, '?', '?');

                    return new Violation(
                        sprintf(
                            'Package "%s" provides violating package "%s".',
                            $package->getName(),
                            $violation->getPackage()->getName()
                        ),
                        new Candidate(
                            $package,
                            $violation->getSymbols()
                        )
                    );
                },
                $this->getDependents(
                    $violation->getPackage()->getName()
                )
            ),
            function (bool $carry, ViolationInterface $violation): bool {
                return $carry || $this->filter->__invoke($violation);
            },
            false
        );
    }

    /**
     * Get a list of dependents for the given package.
     *
     * @param string $package
     *
     * @return string[]
     */
    private function getDependents(string $package): array
    {
        if ($this->graph === null) {
            $this->graph = $this->resolveGraph(
                ...($this->locker->getLockData()['packages'] ?? [])
            );
        }

        return $this->graph[$package] ?? [];
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
