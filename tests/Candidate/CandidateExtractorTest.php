<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Tests\Candidate;

use Composer\Autoload\ClassLoader;
use Composer\Composer;
use Composer\Config;
use Composer\Package\PackageInterface;
use Composer\Repository\RepositoryManager;
use Composer\Repository\WritableRepositoryInterface;
use Mediact\DependencyGuard\Candidate\CandidateInterface;
use Mediact\DependencyGuard\Php\SymbolInterface;
use Mediact\DependencyGuard\Php\SymbolIteratorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Mediact\DependencyGuard\Candidate\CandidateExtractor;

/**
 * @coversDefaultClass \Mediact\DependencyGuard\Candidate\CandidateExtractor
 */
class CandidateExtractorTest extends TestCase
{
    /**
     * @dataProvider extractProvider
     *
     * @param Composer                $composer
     * @param SymbolIteratorInterface $symbols
     * @param int                     $expected
     *
     * @return void
     *
     * @covers ::extract
     * @covers ::extractPackage
     */
    public function testExtract(
        Composer $composer,
        SymbolIteratorInterface $symbols,
        int $expected
    ): void {
        $subject    = new CandidateExtractor();
        $candidates = $subject->extract($composer, $symbols);

        $this->assertCount($expected, $candidates);

        foreach ($candidates as $candidate) {
            $this->assertInstanceOf(CandidateInterface::class, $candidate);
        }
    }

    /**
     * @return Composer[][]|SymbolIteratorInterface[][]|int[][]
     */
    public function extractProvider(): array
    {
        $config = $this->createMock(Config::class);

        $config
            ->expects(self::any())
            ->method('get')
            ->with('vendor-dir', 0)
            ->willReturn(
                realpath(
                    __DIR__ . '/../../vendor'
                )
            );

        return [
            [
                $this->createComposer(
                    $config,
                    $this->createRepository()
                ),
                $this->createSymbolIterator(),
                0
            ],
            [
                $this->createComposer(
                    $config,
                    $this->createRepository()
                ),
                $this->createSymbolIterator(
                    // Code outside vendor.
                    $this->createSymbol(CandidateExtractor::class),
                    // Code inside vendor.
                    $this->createSymbol(Composer::class),
                    // Core code, inside vendor, outside a vendor package.
                    $this->createSymbol(ClassLoader::class)
                ),
                0
            ],
            [
                $this->createComposer(
                    $config,
                    $this->createRepository(
                        $this->createPackage('composer/composer')
                    )
                ),
                $this->createSymbolIterator(),
                0
            ],
            [
                $this->createComposer(
                    $config,
                    $this->createRepository(
                        $this->createPackage('composer/composer')
                    )
                ),
                $this->createSymbolIterator(
                    $this->createSymbol(Composer::class)
                ),
                1
            ],
            [
                $this->createComposer(
                    $config,
                    $this->createRepository(
                        $this->createPackage('composer/composer')
                    )
                ),
                // Multiple symbols per package.
                $this->createSymbolIterator(
                    $this->createSymbol(Composer::class),
                    $this->createSymbol(Composer::class)
                ),
                1
            ],
            [
                $this->createComposer(
                    $config,
                    // Contains duplicate packages.
                    $this->createRepository(
                        $this->createPackage('composer/composer'),
                        $this->createPackage('composer/composer')
                    )
                ),
                // Multiple symbols per package.
                $this->createSymbolIterator(
                    $this->createSymbol(Composer::class),
                    $this->createSymbol(Composer::class)
                ),
                1
            ]
        ];
    }

    /**
     * @param string $name
     *
     * @return SymbolInterface
     */
    private function createSymbol(string $name): SymbolInterface
    {
        /** @var SymbolInterface|MockObject $symbol */
        $symbol = $this->createMock(SymbolInterface::class);

        $symbol
            ->expects(self::any())
            ->method('getName')
            ->willReturn($name);

        return $symbol;
    }

    /**
     * @param SymbolInterface ...$symbols
     *
     * @return SymbolIteratorInterface
     */
    private function createSymbolIterator(
        SymbolInterface ...$symbols
    ): SymbolIteratorInterface {
        /** @var SymbolIteratorInterface|MockObject $iterator */
        $iterator = $this->createMock(SymbolIteratorInterface::class);
        $valid    = array_fill(0, count($symbols), true);
        $valid[]  = false;

        $iterator
            ->expects(self::exactly(count($valid)))
            ->method('valid')
            ->willReturn(...$valid);

        $iterator
            ->expects(self::exactly(count($symbols)))
            ->method('current')
            ->willReturnOnConsecutiveCalls(...$symbols);

        return $iterator;
    }

    /**
     * @param string $name
     *
     * @return PackageInterface
     */
    private function createPackage(string $name): PackageInterface
    {
        /** @var PackageInterface|MockObject $package */
        $package = $this->createMock(PackageInterface::class);

        $package
            ->expects(self::any())
            ->method('getName')
            ->willReturn($name);

        return $package;
    }

    /**
     * @param PackageInterface ...$packages
     *
     * @return WritableRepositoryInterface
     */
    private function createRepository(
        PackageInterface ...$packages
    ): WritableRepositoryInterface {
        /** @var WritableRepositoryInterface|MockObject $repository */
        $repository = $this->createMock(WritableRepositoryInterface::class);

        $repository
            ->expects(self::any())
            ->method('getPackages')
            ->willReturn($packages);

        return $repository;
    }

    /**
     * @param Config                      $config
     * @param WritableRepositoryInterface $localRepository
     *
     * @return Composer
     */
    private function createComposer(
        Config $config,
        WritableRepositoryInterface $localRepository
    ): Composer {
        $repositoryManager = $this->createMock(RepositoryManager::class);

        $repositoryManager
            ->expects(self::any())
            ->method('getLocalRepository')
            ->willReturn($localRepository);

        /** @var Composer|MockObject $composer */
        $composer = $this->createMock(Composer::class);

        $composer
            ->expects(self::any())
            ->method('getConfig')
            ->willReturn($config);

        $composer
            ->expects(self::any())
            ->method('getRepositoryManager')
            ->willReturn($repositoryManager);

        return $composer;
    }
}
