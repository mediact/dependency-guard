<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard;

use Composer\Composer;

class ViolationFilter implements ViolationFilterInterface
{
    /** @var string[] */
    private $ignorePatterns;

    /**
     * Constructor.
     *
     * @param Composer $composer
     */
    public function __construct(Composer $composer)
    {
        $extra = $composer->getPackage()->getExtra();

        $this->ignorePatterns = $extra['dependency-guard']['ignore'] ?? [];
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
        $packageName = $violation->getPackage()->getName();

        return array_reduce(
            $this->ignorePatterns,
            function (
                bool $carry,
                string $ignorePattern
            ) use (
                $packageName
            ): bool {
                return ($carry
                    && $packageName !== $ignorePattern
                    && !fnmatch(
                        $ignorePattern,
                        $packageName,
                        FNM_PATHNAME | FNM_NOESCAPE
                    )
                    && !(
                        strpos($packageName, $ignorePattern) === 0
                        && preg_match('#/$#', $ignorePattern)
                    )
                );
            },
            true
        );
    }
}
