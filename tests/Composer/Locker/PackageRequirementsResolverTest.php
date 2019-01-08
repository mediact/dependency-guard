<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Tests\Composer\Locker;

use Composer\Package\Locker;
use Composer\Package\PackageInterface;
use Composer\Repository\RepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Mediact\DependencyGuard\Composer\Locker\PackageRequirementsResolver;
use SplObjectStorage;

/**
 * @coversDefaultClass \Mediact\DependencyGuard\Composer\Locker\PackageRequirementsResolver
 */
class PackageRequirementsResolverTest extends TestCase
{
    /**
     * @return void
     *
     * @covers ::__construct
     */
    public function testConstructor(): void
    {
        $this->assertInstanceOf(
            PackageRequirementsResolver::class,
            new PackageRequirementsResolver()
        );

        $this->assertInstanceOf(
            PackageRequirementsResolver::class,
            new PackageRequirementsResolver(
                $this->createMock(SplObjectStorage::class)
            )
        );
    }

    /**
     * @dataProvider lockerProvider
     *
     * @param string $package
     * @param Locker $locker
     * @param string ...$expected
     *
     * @return void
     *
     * @covers ::getDependents
     * @covers ::resolve
     * @covers ::resolveGraph
     */
    public function testGetDependents(
        string $package,
        Locker $locker,
        string ...$expected
    ): void {
        $storage = new SplObjectStorage();
        $subject = new PackageRequirementsResolver($storage);

        $this->assertFalse($storage->contains($locker));

        $result = $subject->getDependents($package, $locker);
        $this->assertTrue($storage->contains($locker));
        $this->assertIsArray($result);
        $this->assertCount(count($expected), $result);
        $this->assertEquals($result, $subject->getDependents($package, $locker));

        foreach ($result as $package) {
            $this->assertInstanceOf(PackageInterface::class, $package);
            $this->assertTrue(
                in_array($package->getName(), $expected, true)
            );
        }
    }

    /**
     * @return array
     */
    public function lockerProvider(): array
    {
        return [
            [
                'foo/foo',
                $this->createLocker()
            ],
            [
                'bar/bar',
                $this->createLocker(
                    [
                        'name' => 'foo/foo',
                        'require' => [
                            'php' => '^7.1',
                            'ext-SPL' => '^7.1',
                            'bar/bar' => '@stable'
                        ]
                    ]
                ),
                'foo/foo'
            ],
            [
                'bar/bar',
                $this->createLocker(
                    [
                        'name' => 'foo/foo',
                        'require' => [
                            'php' => '^7.1',
                            'ext-SPL' => '^7.1',
                            'bar/bar' => '@stable'
                        ]
                    ],
                    [
                        'name' => 'baz/baz',
                        'require' => [
                            'foo/foo' => '@stable'
                        ]
                    ]
                ),
                'foo/foo',
                'baz/baz'
            ]
        ];
    }

    /**
     * @param array ...$packages
     *
     * @return Locker
     */
    private function createLocker(array ...$packages): Locker
    {
        /** @var Locker|MockObject $locker */
        $locker = $this->createMock(Locker::class);

        $locker
            ->expects(self::once())
            ->method('getLockData')
            ->willReturn(['packages' => $packages]);

        $repository = $this->createMock(RepositoryInterface::class);

        $locker
            ->expects(self::once())
            ->method('getLockedRepository')
            ->willReturn($repository);

        $repository
            ->expects(self::once())
            ->method('getPackages')
            ->willReturn(
                array_map(
                    function (array $meta): PackageInterface {
                        /** @var PackageInterface|MockObject $package */
                        $package = $this->createMock(PackageInterface::class);

                        $package
                            ->expects(self::any())
                            ->method('getName')
                            ->willReturn($meta['name'] ?? 'unknown');

                        return $package;
                    },
                    $packages
                )
            );

        return $locker;
    }
}
