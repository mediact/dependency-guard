<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard;

use Composer\Composer;
use Composer\Package\PackageInterface;
use Mediact\DependencyGuard\Php\SymbolInterface;
use Mediact\DependencyGuard\Php\SymbolIterator;
use Mediact\DependencyGuard\Php\SymbolIteratorInterface;
use ReflectionClass;

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
