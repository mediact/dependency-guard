<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Candidate;

use Composer\Composer;
use Composer\Package\PackageInterface;
use Mediact\DependencyGuard\Php\SymbolInterface;
use Mediact\DependencyGuard\Php\SymbolIterator;
use Mediact\DependencyGuard\Php\SymbolIteratorInterface;
use Roave\BetterReflection\Reflection\ReflectionClass;

class CandidateExtractor implements CandidateExtractorInterface
{
    /**
     * Extract violation candidates from the given Composer instance and symbols.
     *
     * @param Composer                $composer
     * @param SymbolIteratorInterface $symbols
     *
     * @return iterable|CandidateInterface[]
     */
    public function extract(
        Composer $composer,
        SymbolIteratorInterface $symbols
    ): iterable {
        $repository = $composer->getRepositoryManager()->getLocalRepository();
        $vendorPath = $composer->getConfig()->get('vendor-dir', 0);

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

        $installed = $repository->getPackages();

        $candidates = [];

        foreach ($packages as $name => $symbols) {
            $package = $this->getPackageByName($installed, $name);

            if ($package === null) {
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
     * @param PackageInterface[]|iterable $packages
     * @param string                      $name
     *
     * @return PackageInterface|null
     */
    private function getPackageByName(iterable $packages, string $name): ?PackageInterface
    {
        foreach ($packages as $package) {
            if ($package->getName() === $name) {
                return $package;
            }
        }

        return null;
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
            $reflection = ReflectionClass::createFromName($name);
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
