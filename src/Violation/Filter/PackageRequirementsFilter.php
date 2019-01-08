<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Violation\Filter;

use Composer\Package\Locker;
use Composer\Package\PackageInterface;
use Mediact\DependencyGuard\Candidate\Candidate;
use Mediact\DependencyGuard\Composer\Locker\PackageRequirementsResolver;
use Mediact\DependencyGuard\Composer\Locker\PackageRequirementsResolverInterface;
use Mediact\DependencyGuard\Violation\Violation;
use Mediact\DependencyGuard\Violation\ViolationInterface;

class PackageRequirementsFilter implements ViolationFilterInterface
{
    /** @var Locker */
    private $locker;

    /** @var ViolationFilterInterface */
    private $filter;

    /** @var PackageRequirementsResolverInterface */
    private $resolver;

    /**
     * Constructor.
     *
     * @param Locker                                    $locker
     * @param ViolationFilterInterface                  $filter
     * @param PackageRequirementsResolverInterface|null $resolver
     */
    public function __construct(
        Locker $locker,
        ViolationFilterInterface $filter,
        PackageRequirementsResolverInterface $resolver = null
    ) {
        $this->locker   = $locker;
        $this->filter   = $filter;
        $this->resolver = $resolver ?? new PackageRequirementsResolver();
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
                    PackageInterface $package
                ) use ($violation): ViolationInterface {
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
                $this->resolver->getDependents(
                    $violation->getPackage()->getName(),
                    $this->locker
                )
            ),
            function (bool $carry, ViolationInterface $violation): bool {
                return $carry || $this->filter->__invoke($violation);
            },
            false
        );
    }
}
