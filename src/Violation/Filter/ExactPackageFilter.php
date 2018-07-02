<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Violation\Filter;

use Mediact\DependencyGuard\Violation\ViolationInterface;

class ExactPackageFilter implements ViolationFilterInterface
{
    /** @var string */
    private $package;

    /**
     * Constructor.
     *
     * @param string $package
     */
    public function __construct(string $package)
    {
        $this->package = $package;
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
        return $violation->getPackage()->getName() !== $this->package;
    }
}
