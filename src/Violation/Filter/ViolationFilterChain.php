<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Violation\Filter;

use Mediact\DependencyGuard\Violation\ViolationInterface;

class ViolationFilterChain implements ViolationFilterInterface
{
    /** @var ViolationFilterInterface[] */
    private $filters;

    /**
     * Constructor.
     *
     * @param ViolationFilterInterface ...$filters
     */
    public function __construct(ViolationFilterInterface ...$filters)
    {
        $this->filters = $filters;
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
        foreach ($this->filters as $filter) {
            if (!$filter->__invoke($violation)) {
                return false;
            }
        }

        return true;
    }
}
