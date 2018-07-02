<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard;

interface DependencyGuardFactoryInterface
{
    /**
     * Create a new dependency guard.
     *
     * @return DependencyGuardInterface
     */
    public function create(): DependencyGuardInterface;
}
