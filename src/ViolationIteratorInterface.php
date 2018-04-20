<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard;

use Countable;
use Iterator;

interface ViolationIteratorInterface extends Iterator, Countable
{
    /**
     * Get the current violation.
     *
     * @return ViolationInterface
     */
    public function current(): ViolationInterface;

    /**
     * Get the number of violations.
     *
     * @return int
     */
    public function count(): int;
}
