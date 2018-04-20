<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard;

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
