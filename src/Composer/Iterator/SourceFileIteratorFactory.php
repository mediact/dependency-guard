<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\Prodep\Composer\Iterator;

use AppendIterator;
use ArrayIterator;
use Composer\Composer;
use FilterIterator;
use Iterator;
use Mediact\Prodep\Iterator\FileIterator;
use Mediact\Prodep\Iterator\FileIteratorFactoryInterface;
use Mediact\Prodep\Iterator\FileIteratorInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class SourceFileIteratorFactory implements FileIteratorFactoryInterface
{
    /**
     * Get an iterable list of source files for the root package of the given
     * Composer instance.
     *
     * @param Composer $composer
     *
     * @return FileIteratorInterface
     */
    public function create(Composer $composer): FileIteratorInterface
    {
        $config              = $composer->getConfig();
        $autoloadGenerator   = $composer->getAutoloadGenerator();
        $installationManager = $composer->getInstallationManager();
        $package             = $composer->getPackage();

        $autoloadGenerator->setClassMapAuthoritative(
            $config->get('classmap-authoritative')
        );
        $autoloadGenerator->setDevMode(false);

        $packageMap = $autoloadGenerator->buildPackageMap(
            $installationManager,
            $package,
            [$package]
        );

        $directives = $autoloadGenerator->parseAutoloads($packageMap, $package);

        $files = new AppendIterator();

        $files->append(
            $this->createClassmapIterator(
                $directives['classmap'] ?? [],
                $directives['exclude-from-classmap'] ?? []
            )
        );
        $files->append(
            $this->createFilesIterator(
                ...array_values($directives['files'] ?? [])
            )
        );
        $files->append(
            $this->createNamespaceIterator($directives['psr-0'] ?? [])
        );
        $files->append(
            $this->createNamespaceIterator($directives['psr-4'] ?? [])
        );

        return new FileIterator($files);
    }

    /**
     * @param string ...$paths
     *
     * @return Iterator
     */
    private function createFilesIterator(string ...$paths): Iterator
    {
        return new ArrayIterator(
            array_map(
                function (string $path) : SplFileInfo {
                    return new SplFileInfo($path);
                },
                array_filter(
                    $paths,
                    function (string $path) : bool {
                        return is_readable($path);
                    }
                )
            )
        );
    }

    /**
     * Create an iterator for the given namespaces.
     *
     * @param array $namespaces
     *
     * @return Iterator
     */
    private function createNamespaceIterator(array $namespaces): Iterator
    {
        $files = new AppendIterator();

        foreach ($namespaces as $classmap) {
            $files->append(
                $this->createClassmapIterator($classmap)
            );
        }

        return $files;
    }

    /**
     * Create a class map iterator using the given class maps and exclude patterns.
     *
     * @param iterable|string[] $classmap
     * @param iterable|string[] $exclude
     *
     * @return Iterator|SplFileInfo[]
     */
    private function createClassmapIterator(
        iterable $classmap,
        iterable $exclude = []
    ): Iterator {
        $files = new AppendIterator();

        foreach ($classmap as $directory) {
            if (!is_dir($directory)) {
                continue;
            }

            $files->append(
                new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($directory)
                )
            );
        }

        return new class ($files, ...$exclude) extends FilterIterator {
            /** @var string|null */
            private $excludePattern;

            /**
             * Constructor.
             *
             * @param Iterator $iterator
             * @param string   ...$excludePatterns
             */
            public function __construct(
                Iterator $iterator,
                string ...$excludePatterns
            ) {
                parent::__construct($iterator);

                if (!empty($excludePatterns)) {
                    $this->excludePattern = sprintf(
                        '@^(%s)$@',
                        implode('|', $excludePatterns)
                    );
                }
            }

            /**
             * Check whether the current element of the iterator is acceptable.
             *
             * @return bool
             */
            public function accept(): bool
            {
                /** @var SplFileInfo $file */
                $file = $this->getInnerIterator()->current();

                return (
                    $file->isFile()
                    && (
                        $this->excludePattern === null
                        ?: !preg_match(
                            $this->excludePattern,
                            $file->getRealPath()
                        )
                    )
                );
            }
        };
    }
}
