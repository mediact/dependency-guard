<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Violation\Filter;

use Mediact\DependencyGuard\Violation\ViolationInterface;

class PatternPackageFilter implements ViolationFilterInterface
{
    /** @var string */
    private $pattern;

    /**
     * Constructor.
     *
     * @param string $pattern
     */
    public function __construct(string $pattern)
    {
        $this->pattern = $pattern;
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
        return !fnmatch(
            $this->pattern,
            $violation->getPackage()->getName(),
            FNM_PATHNAME | FNM_NOESCAPE
        );
    }
}
