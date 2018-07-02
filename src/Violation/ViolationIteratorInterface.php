<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Violation;

use Countable;
use Iterator;
use JsonSerializable;

interface ViolationIteratorInterface extends
    Iterator,
    Countable,
    JsonSerializable
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
