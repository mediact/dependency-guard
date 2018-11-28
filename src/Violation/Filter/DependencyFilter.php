<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Violation\Filter;

use Composer\Package\PackageInterface;
use Composer\Repository\CompositeRepository;
use Composer\Repository\RepositoryInterface;
use Mediact\DependencyGuard\Candidate\Candidate;
use Mediact\DependencyGuard\Composer\Repository\Dependent;
use Mediact\DependencyGuard\Violation\Violation;
use Mediact\DependencyGuard\Violation\ViolationInterface;

class DependencyFilter implements ViolationFilterInterface
{
    /** @var ViolationFilterInterface */
    private $filter;

    /** @var CompositeRepository */
    private $repository;

    /** @var Dependent */
    private $dependent;

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
        $this->dependent  = new Dependent($repository);
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
                $this->dependent->getDependents(
                    $violation->getPackage()->getName(),
                    false,
                    []
                )
            ),
            function (bool $carry, ViolationInterface $violation): bool {
                return $carry || $this->filter->__invoke($violation);
            },
            false
        );
    }
}
