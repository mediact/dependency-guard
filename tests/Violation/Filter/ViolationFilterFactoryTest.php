<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Tests\Violation\Filter;

use Composer\Composer;
use Composer\Package\Locker;
use Composer\Package\RootPackageInterface;
use Mediact\DependencyGuard\Violation\Filter\ViolationFilterInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Mediact\DependencyGuard\Violation\Filter\ViolationFilterFactory;

/**
 * @coversDefaultClass \Mediact\DependencyGuard\Violation\Filter\ViolationFilterFactory
 */
class ViolationFilterFactoryTest extends TestCase
{
    /**
     * @dataProvider emptyProvider
     * @dataProvider suggestsProvider
     * @dataProvider extraProvider
     * @dataProvider combinedProvider
     *
     * @param Composer $composer
     *
     * @return void
     *
     * @covers ::create
     * @covers ::getSuggestsFilters
     * @covers ::getIgnoreFilters
     */
    public function testCreate(Composer $composer): void
    {
        $subject = new ViolationFilterFactory();

        $this->assertInstanceOf(
            ViolationFilterInterface::class,
            $subject->create($composer)
        );
    }

    /**
     * @param RootPackageInterface $package
     *
     * @return Composer
     */
    private function createComposer(
        RootPackageInterface $package
    ): Composer {
        /** @var Composer|MockObject $composer */
        $composer = $this->createMock(Composer::class);

        $composer
            ->expects(self::any())
            ->method('getPackage')
            ->willReturn($package);

        $locker = $this->createMock(Locker::class);

        $composer
            ->expects(self::any())
            ->method('getLocker')
            ->willReturn($locker);

        return $composer;
    }

    /**
     * @param array $suggests
     * @param array $extra
     *
     * @return RootPackageInterface
     */
    private function createPackage(
        array $suggests = [],
        array $extra = []
    ): RootPackageInterface {
        /** @var RootPackageInterface|MockObject $package */
        $package = $this->createMock(RootPackageInterface::class);

        $package
            ->expects(self::any())
            ->method('getSuggests')
            ->willReturn($suggests);

        $package
            ->expects(self::any())
            ->method('getExtra')
            ->willReturn($extra);

        return $package;
    }

    /**
     * @return Composer[][]
     */
    public function emptyProvider(): array
    {
        return [
            [
                $this->createComposer(
                    $this->createPackage()
                )
            ]
        ];
    }

    /**
     * @return Composer[][]
     */
    public function suggestsProvider(): array
    {
        return [
            [
                $this->createComposer(
                    $this->createPackage(
                        ['mediact/dependency-guard']
                    )
                )
            ],
            [
                $this->createComposer(
                    $this->createPackage(
                        [
                            'mediact/dependency-guard',
                            'symfony/symfony'
                        ]
                    )
                )
            ],
            [
                $this->createComposer(
                    $this->createPackage(
                        [
                            'mediact/dependency-guard',
                            'symfony/symfony',
                            'nikic/php-parser'
                        ]
                    )
                )
            ]
        ];
    }

    /**
     * @return Composer[][]
     */
    public function extraProvider(): array
    {
        return [
            [
                $this->createComposer(
                    $this->createPackage(
                        [],
                        [
                            'dependency-guard' => [
                                'ignore' => []
                            ]
                        ]
                    )
                )
            ],
            [
                $this->createComposer(
                    $this->createPackage(
                        [],
                        [
                            'dependency-guard' => [
                                'ignore' => [
                                    'symfony/'
                                ]
                            ]
                        ]
                    )
                )
            ],
            [
                $this->createComposer(
                    $this->createPackage(
                        [],
                        [
                            'dependency-guard' => [
                                'ignore' => [
                                    'symfony/symfony'
                                ]
                            ]
                        ]
                    )
                )
            ],
            [
                $this->createComposer(
                    $this->createPackage(
                        [],
                        [
                            'dependency-guard' => [
                                'ignore' => [
                                    'symfony/symfony',
                                    'mediact/dependency-guard'
                                ]
                            ]
                        ]
                    )
                )
            ],
            [
                $this->createComposer(
                    $this->createPackage(
                        [],
                        [
                            'dependency-guard' => [
                                'ignore' => [
                                    'symfony/symfony',
                                    'mediact/dependency-guard',
                                    'nikic/php-parser'
                                ]
                            ]
                        ]
                    )
                )
            ]
        ];
    }

    /**
     * @return Composer[][]
     */
    public function combinedProvider(): array
    {
        return [
            [
                $this->createComposer(
                    $this->createPackage(
                        [
                            'symfony/symfony',
                            'mediact/dependency-guard',
                            'nikic/php-parser'
                        ],
                        [
                            'dependency-guard' => [
                                'ignore' => [
                                    'symfony/symfony',
                                    'mediact/dependency-guard',
                                    'nikic/php-parser'
                                ]
                            ]
                        ]
                    )
                )
            ]
        ];
    }
}
