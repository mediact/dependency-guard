<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Violation\Filter;

use Mediact\DependencyGuard\Violation\ViolationInterface;

class VendorFilter implements ViolationFilterInterface
{
    /** @var string */
    private $vendor;

    /**
     * Constructor.
     *
     * @param string $vendor
     */
    public function __construct(string $vendor)
    {
        $this->vendor = rtrim($vendor, '/') . '/';
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
        return strpos(
            $violation->getPackage()->getName(),
            $this->vendor
        ) !== 0;
    }
}
