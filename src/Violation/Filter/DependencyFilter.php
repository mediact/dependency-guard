<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Violation\Filter;

use Composer\Composer;
use Composer\Package\PackageInterface;
use Composer\Repository\CompositeRepository;
use Composer\Repository\RepositoryInterface;
use Mediact\DependencyGuard\Candidate\Candidate;
use Mediact\DependencyGuard\Violation\Violation;
use Mediact\DependencyGuard\Violation\ViolationInterface;

class DependencyFilter implements ViolationFilterInterface
{
    /** @var ViolationFilterInterface */
    private $filter;

    /** @var CompositeRepository */
    private $repository;

    /** @var array */
    private $dependencies;

    /**
     * Constructor.
     *
     * @param RepositoryInterface      $repository
     * @param ViolationFilterInterface $filter
     */
    public function __construct(
        RepositoryInterface $repository,
        ViolationFilterInterface $filter
    ) {
        $this->filter     = $filter;
        $this->repository = new CompositeRepository([$repository]);
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
                    array $dependent
                ) use ($violation): ViolationInterface {
                    /** @var PackageInterface $package */
                    [$package] = $dependent;
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
                $this->getDependencies($violation->getPackage()->getName())
            ),
            function (bool $carry, ViolationInterface $violation): bool {
                return $carry || $this->filter->__invoke($violation);
            },
            false
        );
    }

    /**
     * Retrieves the dependencies of a package in a recursive way.
     *
     * @param string $packageName
     *
     * @return array
     */
    private function getDependencies(string $packageName): array
    {
        if (!isset($this->dependencies[$packageName])) {
            $this->dependencies[$packageName] = $this->repository->getDependents(
                $packageName,
                null,
                false,
                false
            );

            foreach ($this->dependencies[$packageName] as $key => $dependent) {
                $this->dependencies[$packageName] = array_merge(
                    $this->dependencies[$packageName],
                    $this->getDependencies($key)
                );
            }
        }

        return $this->dependencies[$packageName];
    }
}
