<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Tests\Composer\Repository;

use Composer\Repository\CompositeRepository;
use Composer\Repository\RepositoryInterface;
use Mediact\DependencyGuard\Composer\Repository\DependentsResolver;
use PHPUnit\Framework\TestCase;
use Composer\Package\Link;
use Composer\Package\PackageInterface;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @coversDefaultClass \Mediact\DependencyGuard\Composer\Repository\DependentsResolver
 */
class DependentResolverTest extends TestCase
{
    /**
     * @return void
     *
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $this->assertInstanceOf(
            DependentsResolver::class,
            new DependentsResolver(
                $this->createMock(RepositoryInterface::class)
            )
        );
    }

    /**
     * @dataProvider repositoryProvider
     *
     * @param RepositoryInterface $repository
     * @param string              $packageName
     * @param array               $expected
     *
     * @return void
     *
     * @covers ::resolve
     * @covers ::getDependents
     */
    public function testResolve(
        RepositoryInterface $repository,
        string $packageName,
        array $expected
    ): void {
        $subject = new DependentsResolver($repository);
        $this->assertEquals(
            $expected,
            array_keys($subject->resolve($packageName))
        );
    }

    /**
     * @dataProvider repositoryProvider
     *
     * @param RepositoryInterface $repository
     * @param string              $packageName
     *
     * @return void
     *
     * @covers ::getDependents
     */
    public function testEqualBehaviour(
        RepositoryInterface $repository,
        string $packageName
    ): void {
        $subject = new DependentsResolver($repository);
        $legacy  = new CompositeRepository([$repository]);
        $this->assertEquals(
            $legacy->getDependents($packageName),
            $subject->resolve($packageName)
        );
    }

    /**
     * @param PackageInterface ...$packages
     *
     * @return RepositoryInterface
     */
    private function createRepository(
        PackageInterface ...$packages
    ): RepositoryInterface {
        /** @var RepositoryInterface|MockObject $repository */
        $repository = $this->createMock(RepositoryInterface::class);

        $repository
            ->expects(self::any())
            ->method('getPackages')
            ->willReturn($packages);

        return $repository;
    }

    /**
     * @param string $name
     * @param string ...$requires
     *
     * @return PackageInterface
     */
    private function createPackage(
        string $name,
        string ...$requires
    ): PackageInterface {
        /** @var PackageInterface|MockObject $package */
        $package = $this->createMock(PackageInterface::class);

        $package
            ->expects(self::any())
            ->method('getName')
            ->willReturn($name);

        $package
            ->expects(self::any())
            ->method('getRequires')
            ->willReturn(
                array_map(
                    function (string $target) use ($name): Link {
                        return $this->createLink($name, $target);
                    },
                    $requires
                )
            );

        $package
            ->expects(self::any())
            ->method(
                self::matchesRegularExpression(
                    '/^get(DevRequires|Replaces|Conflicts)$/'
                )
            )
            ->willReturn([]);

        return $package;
    }

    /**
     * @param string $source
     * @param string $target
     *
     * @return Link
     */
    private function createLink(string $source, string $target): Link
    {
        /** @var Link|MockObject $link */
        $link = $this->createMock(Link::class);

        $link
            ->expects(self::any())
            ->method('getSource')
            ->willReturn($source);

        $link
            ->expects(self::any())
            ->method('getTarget')
            ->willReturn($target);

        return $link;
    }

    /**
     * @return array
     */
    public function repositoryProvider(): array
    {
        return [
            [
                $this->createRepository(),
                'foo/foo',
                []
            ],
            [
                $this->createRepository(
                    $this->createPackage('bar/bar')
                ),
                'foo/foo',
                []
            ],
            [
                $this->createRepository(
                    $this->createPackage(
                        'bar/bar',
                        'foo/foo'
                    )
                ),
                'foo/foo',
                ['bar/bar']
            ],
            [
                $this->createRepository(
                    $this->createPackage(
                        'bar/bar'
                    )
                ),
                'foo/foo',
                []
            ],
            [
                $this->createRepository(
                    $this->createPackage(
                        'bar/bar',
                        'foo/foo',
                        'baz/baz'
                    ),
                    $this->createPackage(
                        'baz/baz',
                        'qux/qux'
                    ),
                    $this->createPackage(
                        'quz/quz',
                        'foo/foo'
                    )
                ),
                'foo/foo',
                ['bar/bar', 'quz/quz']
            ]
        ];
    }
}
