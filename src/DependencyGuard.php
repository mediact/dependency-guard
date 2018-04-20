<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard;

use Composer\Composer;
use Composer\Package\Locker;
use Composer\Package\PackageInterface;
use Composer\Repository\RepositoryInterface;
use Mediact\DependencyGuard\Composer\Iterator\SourceFileIteratorFactory;
use Mediact\DependencyGuard\Iterator\FileIteratorFactoryInterface;
use Mediact\DependencyGuard\Php\SymbolExtractor;
use Mediact\DependencyGuard\Php\SymbolExtractorInterface;
use Mediact\DependencyGuard\Php\SymbolInterface;
use Mediact\DependencyGuard\Php\SymbolIterator;
use Mediact\DependencyGuard\Php\SymbolIteratorInterface;
use ReflectionClass;

class DependencyGuard implements DependencyGuardInterface
{
    /** @var FileIteratorFactoryInterface */
    private $sourceFileFactory;

    /** @var SymbolExtractorInterface */
    private $extractor;

    /**
     * Constructor.
     *
     * @param FileIteratorFactoryInterface|null $sourceFileFactory
     * @param SymbolExtractorInterface|null     $extractor
     */
    public function __construct(
        FileIteratorFactoryInterface $sourceFileFactory = null,
        SymbolExtractorInterface $extractor = null
    ) {
        $this->sourceFileFactory = (
            $sourceFileFactory ?? new SourceFileIteratorFactory()
        );
        $this->extractor         = $extractor ?? new SymbolExtractor();
    }

    /**
     * Determine dependency violations for the given Composer instance.
     *
     * @param Composer $composer
     *
     * @return ViolationIteratorInterface
     */
    public function determineViolations(Composer $composer): ViolationIteratorInterface
    {
        $files      = $this->sourceFileFactory->create($composer);
        $exclusions = $this->getExclusions($composer);
        $symbols    = $this->extractor->extract($files, ...$exclusions);
        $repository = $composer->getRepositoryManager()->getLocalRepository();
        $candidates = $this->extractCandidates(
            $repository,
            $composer->getConfig()->get('vendor-dir', 0),
            $symbols
        );

        $violations = array_merge(
            $this->determineLockViolations(
                $composer->getLocker(),
                ...$candidates
            ),
            $this->determineUnusedCodeViolations(
                $composer->getPackage(),
                $repository,
                ...$candidates
            )
        );

        return new ViolationIterator(
            ...array_filter(
                $violations,
                new ViolationFilter($composer)
            )
        );
    }

    /**
     * Get violations for candidates violating the locked state.
     *
     * @param Locker             $locker
     * @param CandidateInterface ...$candidates
     *
     * @return ViolationInterface[]
     */
    private function determineLockViolations(
        Locker $locker,
        CandidateInterface ...$candidates
    ): array {
        $violations = [];
        $lock       = $locker->getLockData();

        $lockedPackages = array_map(
            function (array $package) : string {
                return $package['name'];
            },
            $lock['packages']
        );

        $lockedDevPackages = array_map(
            function (array $package) : string {
                return $package['name'];
            },
            $lock['packages-dev']
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
     * @param PackageInterface    $package
     * @param RepositoryInterface $repository
     * @param CandidateInterface  ...$candidates
     *
     * @return ViolationInterface[]
     */
    private function determineUnusedCodeViolations(
        PackageInterface $package,
        RepositoryInterface $repository,
        CandidateInterface ...$candidates
    ): array {
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

    /**
     * Get the exclusions from the Composer root package.
     *
     * @param Composer $composer
     *
     * @return string[]
     */
    private function getExclusions(Composer $composer): array
    {
        $extra = $composer->getPackage()->getExtra();

        return $extra['dependency-guard']['exclude'] ?? [];
    }

    /**
     * Extract candidates from the given symbols.
     *
     * @param RepositoryInterface     $repository
     * @param string                  $vendorPath
     * @param SymbolIteratorInterface $symbols
     *
     * @return CandidateInterface[]
     */
    private function extractCandidates(
        RepositoryInterface $repository,
        string $vendorPath,
        SymbolIteratorInterface $symbols
    ): array {
        $packages = [];

        foreach ($symbols as $symbol) {
            $package = $this->extractPackage($vendorPath, $symbol);

            if ($package === null) {
                continue;
            }

            if (!array_key_exists($package, $packages)) {
                $packages[$package] = [];
            }

            $packages[$package][] = $symbol;
        }

        $candidates = [];
        $installed  = $repository->getPackages();

        foreach ($packages as $name => $symbols) {
            $package = array_reduce(
                $installed,
                function (
                    ?PackageInterface $carry,
                    PackageInterface $package
                ) use (
                    $name
                ): ?PackageInterface {
                    return $carry ?? (
                        $package->getName() === $name
                            ? $package
                            : null
                    );
                }
            );

            if (!$package instanceof PackageInterface) {
                continue;
            }

            $candidates[] = new Candidate(
                $package,
                new SymbolIterator(...$symbols)
            );
        }

        return $candidates;
    }


    /**
     * Extract the package name from the given PHP symbol.
     *
     * @param string          $vendorPath
     * @param SymbolInterface $symbol
     *
     * @return string|null
     */
    private function extractPackage(
        string $vendorPath,
        SymbolInterface $symbol
    ): ?string {
        static $packagesPerSymbol = [];

        $name = $symbol->getName();

        if (!array_key_exists($name, $packagesPerSymbol)) {
            $reflection = new ReflectionClass($name);
            $file       = $reflection->getFileName();

            // This happens for symbols in the current package.
            if (strpos($file, $vendorPath) !== 0) {
                return null;
            }

            $structure = explode(
                DIRECTORY_SEPARATOR,
                preg_replace(
                    sprintf(
                        '/^%s/',
                        preg_quote($vendorPath . DIRECTORY_SEPARATOR, '/')
                    ),
                    '',
                    $file
                ),
                3
            );

            // This happens when other code extends Composer root code, like:
            // composer/ClassLoader.php
            if (count($structure) < 3) {
                $packagesPerSymbol[$name] = null;
            }

            [$vendor, $package] = $structure;

            $packagesPerSymbol[$name] = sprintf('%s/%s', $vendor, $package);
        }

        return $packagesPerSymbol[$name] ?? null;
    }
}
