<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Violation\Finder;

use Composer\Composer;
use Composer\Package\PackageInterface;
use Mediact\DependencyGuard\Candidate\Candidate;
use Mediact\DependencyGuard\Candidate\CandidateExtractor;
use Mediact\DependencyGuard\Candidate\CandidateExtractorInterface;
use Mediact\DependencyGuard\Candidate\CandidateInterface;
use Mediact\DependencyGuard\Php\SymbolIterator;
use Mediact\DependencyGuard\Php\SymbolIteratorInterface;
use Mediact\DependencyGuard\Violation\Violation;
use Mediact\DependencyGuard\Violation\ViolationInterface;
use Mediact\DependencyGuard\Violation\ViolationIterator;
use Mediact\DependencyGuard\Violation\ViolationIteratorInterface;

class ViolationFinder implements ViolationFinderInterface
{
    /** @var CandidateExtractorInterface */
    private $extractor;

    /**
     * Constructor.
     *
     * @param CandidateExtractorInterface|null $extractor
     */
    public function __construct(CandidateExtractorInterface $extractor = null)
    {
        $this->extractor = $extractor ?? new CandidateExtractor();
    }

    /**
     * Find violations for the given Composer instance and symbols.
     *
     * @param Composer                $composer
     * @param SymbolIteratorInterface $symbols
     *
     * @return ViolationIteratorInterface
     */
    public function find(
        Composer $composer,
        SymbolIteratorInterface $symbols
    ): ViolationIteratorInterface {
        $candidates = $this->extractor->extract($composer, $symbols);

        return new ViolationIterator(
            ...array_merge(
                $this->determineLockViolations($composer, ...$candidates),
                $this->determineUnusedCodeViolations($composer, ...$candidates)
            )
        );
    }

    /**
     * Get violations for candidates violating the locked state.
     *
     * @param Composer           $composer
     * @param CandidateInterface ...$candidates
     *
     * @return ViolationInterface[]
     */
    private function determineLockViolations(
        Composer $composer,
        CandidateInterface ...$candidates
    ): array {
        $violations = [];
        $lock       = $composer->getLocker()->getLockData();

        $lockedPackages = array_map(
            function (array $package) : string {
                return $package['name'];
            },
            $lock['packages'] ?? []
        );

        $lockedDevPackages = array_map(
            function (array $package) : string {
                return $package['name'];
            },
            $lock['packages-dev'] ?? []
        );

        foreach ($candidates as $candidate) {
            $package = $candidate->getPackage()->getName();

            if (in_array($package, $lockedDevPackages, true)) {
                $violations[] = new Violation(
                    sprintf(
                        'Code base is dependent on dev package %s.',
                        $package
                    ),
                    $candidate
                );
                continue;
            }

            if (!in_array($package, $lockedPackages, true)) {
                $violations[] = new Violation(
                    sprintf(
                        'Package is not installed: %s.',
                        $package
                    ),
                    $candidate
                );
            }
        }

        return $violations;
    }

    /**
     * Determine what packages are installed without having its code be used in
     * the given package.
     *
     * @param Composer             $composer
     * @param CandidateInterface[] ...$candidates
     *
     * @return ViolationInterface[]
     */
    private function determineUnusedCodeViolations(
        Composer $composer,
        CandidateInterface ...$candidates
    ): array {
        $repository = $composer->getRepositoryManager()->getLocalRepository();
        $package    = $composer->getPackage();

        $violations   = [];
        $installed    = [];
        $requirements = array_keys($package->getRequires());
        $packages     = array_map(
            function (CandidateInterface $candidate) : string {
                return $candidate->getPackage()->getName();
            },
            $candidates
        );

        foreach ($repository->getPackages() as $package) {
            $installed[$package->getName()] = $package;
        }

        foreach ($requirements as $requirement) {
            // Platform requirements aren't part of this test.
            if (strpos($requirement, '/') === false) {
                continue;
            }

            // The package is never installed.
            if (!array_key_exists($requirement, $installed)) {
                continue;
            }

            /** @var PackageInterface $package */
            $package = $installed[$requirement];

            // A meta package would not introduce code symbols.
            if ($package->getType() === 'metapackage') {
                continue;
            }

            if (!in_array($requirement, $packages, true)) {
                $violations[] = new Violation(
                    sprintf(
                        'Package "%s" is installed, but never used.',
                        $requirement
                    ),
                    new Candidate($package, new SymbolIterator())
                );
            }
        }

        return $violations;
    }
}
