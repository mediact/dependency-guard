<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Violation;

use ArrayIterator;
use IteratorIterator;

class ViolationIterator extends IteratorIterator implements
    ViolationIteratorInterface
{
    /** @var int */
    private $numViolations;

    /**
     * Constructor.
     *
     * @param ViolationInterface ...$violations
     */
    public function __construct(ViolationInterface ...$violations)
    {
        $this->numViolations = count($violations);
        parent::__construct(
            new ArrayIterator($violations)
        );
    }

    /**
     * Get the current violation.
     *
     * @return ViolationInterface
     */
    public function current(): ViolationInterface
    {
        return parent::current();
    }

    /**
     * Get the number of violations.
     *
     * @return int
     */
    public function count(): int
    {
        return $this->numViolations;
    }

    /**
     * Specify data that should be serialized to JSON.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return iterator_to_array($this);
    }
}
