<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Tests\Violation\Filter;

use Composer\Package\Link;
use Composer\Package\PackageInterface;
use Composer\Repository\RepositoryInterface;
use Mediact\DependencyGuard\Violation\Filter\ViolationFilterInterface;
use Mediact\DependencyGuard\Violation\ViolationInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Mediact\DependencyGuard\Violation\Filter\DependencyFilter;

/**
 * @coversDefaultClass \Mediact\DependencyGuard\Violation\Filter\DependencyFilter
 */
class DependencyFilterTest extends TestCase
{
    /**
     * @return void
     *
     * @covers ::__construct
     */
    public function testConstructor(): void
    {
        $this->assertInstanceOf(
            DependencyFilter::class,
            new DependencyFilter(
                $this->createMock(RepositoryInterface::class),
                $this->createMock(ViolationFilterInterface::class)
            )
        );
    }

    /**
     * @dataProvider violationProvider
     *
     * @param RepositoryInterface      $repository
     * @param ViolationFilterInterface $filter
     * @param ViolationInterface       $violation
     * @param bool                     $expected
     *
     * @return void
     *
     * @covers ::__invoke
     * @covers ::getDependents
     */
    public function testInvoke(
        RepositoryInterface $repository,
        ViolationFilterInterface $filter,
        ViolationInterface $violation,
        bool $expected
    ): void {
        $subject = new DependencyFilter($repository, $filter);
        $this->assertEquals($expected, $subject->__invoke($violation));
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
     * @param string ...$accepted
     *
     * @return ViolationFilterInterface
     */
    private function createFilter(string ...$accepted): ViolationFilterInterface
    {
        /** @var ViolationFilterInterface|MockObject $filter */
        $filter = $this->createMock(ViolationFilterInterface::class);

        $filter
            ->expects(self::any())
            ->method('__invoke')
            ->with(self::isInstanceOf(ViolationInterface::class))
            ->willReturnCallback(
                function (ViolationInterface $violation) use ($accepted): bool {
                    return in_array(
                        $violation->getPackage()->getName(),
                        $accepted,
                        true
                    );
                }
            );

        return $filter;
    }

    /**
     * @param string $packageName
     *
     * @return ViolationInterface
     */
    private function createViolation(
        string $packageName
    ): ViolationInterface {
        /** @var ViolationInterface|MockObject $violation */
        $violation = $this->createMock(ViolationInterface::class);

        $violation
            ->expects(self::any())
            ->method('getPackage')
            ->willReturn(
                $this->createPackage($packageName)
            );

        return $violation;
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
     * @return RepositoryInterface[][]|ViolationFilterInterface[][]|ViolationInterface[][]|bool[][]
     */
    public function violationProvider(): array
    {
        return [
            [
                $this->createRepository(),
                $this->createFilter(),
                $this->createViolation('foo/foo'),
                false
            ],
            [
                $this->createRepository(
                    $this->createPackage('bar/bar')
                ),
                $this->createFilter('bar/bar'),
                $this->createViolation('foo/foo'),
                false
            ],
            [
                $this->createRepository(
                    $this->createPackage(
                        'bar/bar',
                        'foo/foo'
                    )
                ),
                $this->createFilter('bar/bar'),
                $this->createViolation('foo/foo'),
                true
            ],
            [
                $this->createRepository(
                    $this->createPackage(
                        'bar/bar'
                    )
                ),
                $this->createFilter('foo/foo'),
                $this->createViolation('foo/foo'),
                false
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
                $this->createFilter('bar/bar'),
                $this->createViolation('foo/foo'),
                true
            ]
        ];
    }
}
