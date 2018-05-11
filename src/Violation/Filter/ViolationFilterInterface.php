<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Violation\Filter;

use Mediact\DependencyGuard\Violation\ViolationInterface;

interface ViolationFilterInterface
{
    /**
     * Filter violations.
     *
     * @param ViolationInterface $violation
     *
     * @return bool
     */
    public function __invoke(ViolationInterface $violation): bool;
}
