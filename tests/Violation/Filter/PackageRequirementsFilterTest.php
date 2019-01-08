<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Tests\Violation\Filter;

use Composer\Package\Locker;
use Composer\Package\PackageInterface;
use Mediact\DependencyGuard\Composer\Locker\PackageRequirementsResolverInterface;
use Mediact\DependencyGuard\Violation\Filter\ViolationFilterInterface;
use Mediact\DependencyGuard\Violation\ViolationInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Mediact\DependencyGuard\Violation\Filter\PackageRequirementsFilter;

/**
 * @coversDefaultClass \Mediact\DependencyGuard\Violation\Filter\PackageRequirementsFilter
 */
class PackageRequirementsFilterTest extends TestCase
{
    /**
     * @return void
     *
     * @covers ::__construct
     */
    public function testConstructor(): void
    {
        $this->assertInstanceOf(
            PackageRequirementsFilter::class,
            new PackageRequirementsFilter(
                $this->createMock(Locker::class),
                $this->createMock(ViolationFilterInterface::class)
            )
        );

        $this->assertInstanceOf(
            PackageRequirementsFilter::class,
            new PackageRequirementsFilter(
                $this->createMock(Locker::class),
                $this->createMock(ViolationFilterInterface::class),
                $this->createMock(PackageRequirementsResolverInterface::class)
            )
        );
    }

    /**
     * @dataProvider violationProvider
     *
     * @param ViolationFilterInterface             $filter
     * @param PackageRequirementsResolverInterface $resolver
     * @param ViolationInterface                   $violation
     * @param bool                                 $expected
     *
     * @return void
     *
     * @covers ::__invoke
     */
    public function testInvoke(
        ViolationFilterInterface $filter,
        PackageRequirementsResolverInterface $resolver,
        ViolationInterface $violation,
        bool $expected
    ): void {
        $subject = new PackageRequirementsFilter(
            $this->createMock(Locker::class),
            $filter,
            $resolver
        );
        $this->assertEquals($expected, $subject->__invoke($violation));
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
     * @return array
     */
    public function violationProvider(): array
    {
        return [
            [
                $this->createFilter(),
                $this->createResolver(),
                $this->createViolation('foo/foo'),
                false
            ],
            [
                $this->createFilter('bar/bar'),
                $this->createResolver(['bar/bar' => []]),
                $this->createViolation('foo/foo'),
                false
            ],
            [
                $this->createFilter('bar/bar'),
                $this->createResolver(
                    [
                        'foo/foo' => [
                            $this->createPackage('bar/bar')
                        ]
                    ]
                ),
                $this->createViolation('foo/foo'),
                true
            ],
            [
                $this->createFilter('foo/foo'),
                $this->createResolver(
                    [
                        'foo/foo' => [
                            $this->createPackage('bar/bar')
                        ]
                    ]
                ),
                $this->createViolation('foo/foo'),
                false
            ],
            [
                $this->createFilter('bar/bar'),
                $this->createResolver(
                    [
                        'foo/foo' => [
                            $this->createPackage('bar/bar')
                        ],
                        'bar/bar' => [],
                        'baz/baz' => [
                            $this->createPackage('bar/bar'),
                        ],
                        'qux/qux' => [
                            $this->createPackage('baz/baz'),
                            $this->createPackage('bar/bar')
                        ]
                    ]
                ),
                $this->createViolation('foo/foo'),
                true
            ]
        ];
    }

    /**
     * @param PackageInterface[][] $graph
     *
     * @return PackageRequirementsResolverInterface
     */
    private function createResolver(
        array $graph = []
    ): PackageRequirementsResolverInterface {
        /** @var PackageRequirementsResolverInterface|MockObject $resolver */
        $resolver = $this->createMock(PackageRequirementsResolverInterface::class);

        $resolver
            ->expects(self::any())
            ->method('getDependents')
            ->with(
                self::isType('string'),
                self::isInstanceOf(Locker::class)
            )
            ->willReturnCallback(
                function (string $package) use ($graph): array {
                    return $graph[$package] ?? [];
                }
            );

        return $resolver;
    }
}
