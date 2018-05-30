<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Tests\Violation\Finder;

use Composer\Composer;
use Composer\Package\Locker;
use Composer\Package\PackageInterface;
use Composer\Package\RootPackageInterface;
use Composer\Repository\RepositoryManager;
use Composer\Repository\WritableRepositoryInterface;
use Mediact\DependencyGuard\Candidate\CandidateExtractorInterface;
use Mediact\DependencyGuard\Candidate\CandidateInterface;
use Mediact\DependencyGuard\Php\SymbolIteratorInterface;
use Mediact\DependencyGuard\Violation\ViolationIteratorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Mediact\DependencyGuard\Violation\Finder\ViolationFinder;

/**
 * @coversDefaultClass \Mediact\DependencyGuard\Violation\Finder\ViolationFinder
 */
class ViolationFinderTest extends TestCase
{
    /**
     * @return void
     *
     * @covers ::__construct
     */
    public function testConstructor(): void
    {
        $this->assertInstanceOf(
            ViolationFinder::class,
            new ViolationFinder(
                $this->createMock(CandidateExtractorInterface::class)
            )
        );

        $this->assertInstanceOf(
            ViolationFinder::class,
            new ViolationFinder()
        );
    }

    /**
     * @dataProvider emptyExtractorProvider
     * @dataProvider lockViolationsProvider
     * @dataProvider unusedCodeViolationsProvider
     *
     * @param CandidateExtractorInterface $extractor
     * @param Composer                    $composer
     * @param int                         $numViolations
     *
     * @return void
     *
     * @covers ::find
     * @covers ::determineLockViolations
     * @covers ::determineUnusedCodeViolations
     */
    public function testFind(
        CandidateExtractorInterface $extractor,
        Composer $composer,
        int $numViolations = 0
    ): void {
        $subject = new ViolationFinder($extractor);

        $violations = $subject->find(
            $composer,
            $this->createMock(SymbolIteratorInterface::class)
        );

        $this->assertInstanceOf(
            ViolationIteratorInterface::class,
            $violations
        );
        $this->assertCount($numViolations, $violations);
    }

    /**
     * @param array $data
     *
     * @return Locker
     */
    private function createLocker(array $data = []): Locker
    {
        /** @var Locker|MockObject $locker */
        $locker = $this->createMock(Locker::class);

        $locker
            ->expects(self::any())
            ->method('getLockData')
            ->willReturn($data);

        return $locker;
    }

    /**
     * @param array $requires
     *
     * @return RootPackageInterface
     */
    private function createRootPackage(
        array $requires = []
    ): RootPackageInterface {
        /** @var RootPackageInterface|MockObject $package */
        $package = $this->createMock(RootPackageInterface::class);

        $package
            ->expects(self::any())
            ->method('getRequires')
            ->willReturn($requires);

        return $package;
    }

    /**
     * @param RootPackageInterface $package
     * @param Locker               $locker
     * @param PackageInterface     ...$packages
     *
     * @return Composer
     */
    private function createComposer(
        RootPackageInterface $package,
        Locker $locker,
        PackageInterface ...$packages
    ): Composer {
        /** @var Composer|MockObject $composer */
        $composer = $this->createMock(Composer::class);

        $composer
            ->expects(self::any())
            ->method('getPackage')
            ->willReturn($package);

        $composer
            ->expects(self::any())
            ->method('getLocker')
            ->willReturn($locker);

        $repositoryManager = $this->createMock(RepositoryManager::class);

        $composer
            ->expects(self::any())
            ->method('getRepositoryManager')
            ->willReturn($repositoryManager);

        $localRepository = $this->createMock(WritableRepositoryInterface::class);

        $repositoryManager
            ->expects(self::any())
            ->method('getLocalRepository')
            ->willReturn($localRepository);

        $localRepository
            ->expects(self::any())
            ->method('getPackages')
            ->willReturn($packages);

        return $composer;
    }

    /**
     * @param CandidateInterface ...$candidates
     *
     * @return CandidateExtractorInterface
     */
    private function createExtractor(
        CandidateInterface ...$candidates
    ): CandidateExtractorInterface {
        /** @var CandidateExtractorInterface|MockObject $extractor */
        $extractor = $this->createMock(CandidateExtractorInterface::class);

        $extractor
            ->expects(self::any())
            ->method('extract')
            ->with(
                self::isInstanceOf(Composer::class),
                self::isInstanceOf(SymbolIteratorInterface::class)
            )
            ->willReturn($candidates);

        return $extractor;
    }

    /**
     * @param string $name
     * @param string $type
     *
     * @return PackageInterface
     */
    private function createPackage(string $name, string $type = 'library'): PackageInterface
    {
        /** @var PackageInterface|MockObject $package */
        $package = $this->createMock(PackageInterface::class);

        $package
            ->expects(self::any())
            ->method('getName')
            ->willReturn($name);

        $package
            ->expects(self::any())
            ->method('getType')
            ->willReturn($type);

        return $package;
    }

    /**
     * @param string $packageName
     *
     * @return CandidateInterface
     */
    private function createCandidate(string $packageName): CandidateInterface
    {
        /** @var CandidateInterface|MockObject $candidate */
        $candidate = $this->createMock(CandidateInterface::class);

        $candidate
            ->expects(self::any())
            ->method('getPackage')
            ->willReturn(
                $this->createPackage($packageName)
            );

        return $candidate;
    }

    /**
     * @return CandidateExtractorInterface[][]|Composer[][]
     */
    public function emptyExtractorProvider(): array
    {
        $locker  = $this->createLocker();
        $package = $this->createRootPackage();

        return [
            [$this->createExtractor(), $this->createComposer($package, $locker)]
        ];
    }

    /**
     * @return CandidateExtractorInterface[][]|Composer[][]|int[][]
     */
    public function lockViolationsProvider(): array
    {
        $package = $this->createRootPackage();

        return [
            [
                $this->createExtractor(
                    $this->createCandidate('foo/bar'),
                    $this->createCandidate('bar/baz')
                ),
                $this->createComposer(
                    $package,
                    $this->createLocker(
                        [
                            'packages' => [
                                ['name' => 'bar/baz']
                            ],
                            'packages-dev' => [
                                ['name' => 'bar/baz']
                            ]
                        ]
                    ),
                    $this->createPackage('foo/bar'),
                    $this->createPackage('bar/baz')
                ),
                2
            ]
        ];
    }

    /**
     * @return CandidateExtractorInterface[][]|Composer[][]|int[][]
     */
    public function unusedCodeViolationsProvider(): array
    {
        return [
            [
                $this->createExtractor(
                    $this->createCandidate('bar/baz')
                ),
                $this->createComposer(
                    $this->createRootPackage(
                        [
                            'php' => '^7.2',
                            'foo/bar' => '^1.0',
                            'bar/baz' => '@stable',
                            'qux/quu' => '@alpha',
                            'quuuuu/quuuu' => '@dev'
                        ]
                    ),
                    $this->createLocker(
                        [
                            'packages' => [
                                ['name' => 'foo/bar'],
                                ['name' => 'bar/baz'],
                                ['name' => 'qux/quu']
                            ]
                        ]
                    ),
                    $this->createPackage('foo/bar', 'metapackage'),
                    $this->createPackage('bar/baz'),
                    $this->createPackage('qux/quu')
                ),
                1
            ]
        ];
    }
}
