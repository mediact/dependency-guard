<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Tests\Php\Filter;

use Composer\Composer;
use Composer\Package\RootPackageInterface;
use Mediact\DependencyGuard\Php\Filter\SymbolFilterInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Mediact\DependencyGuard\Php\Filter\SymbolFilterFactory;

/**
 * @coversDefaultClass \Mediact\DependencyGuard\Php\Filter\SymbolFilterFactory
 */
class SymbolFilterFactoryTest extends TestCase
{
    /**
     * @dataProvider composerProvider
     *
     * @param Composer $composer
     *
     * @return void
     *
     * @covers ::create
     * @covers ::getExclusions
     */
    public function testCreate(Composer $composer): void
    {
        $subject = new SymbolFilterFactory();

        $this->assertInstanceOf(
            SymbolFilterInterface::class,
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

        return $composer;
    }

    /**
     * @param array $extra
     *
     * @return RootPackageInterface
     */
    private function createRootPackage(array $extra): RootPackageInterface
    {
        /** @var RootPackageInterface|MockObject $package */
        $package = $this->createMock(RootPackageInterface::class);

        $package
            ->expects(self::any())
            ->method('getExtra')
            ->willReturn($extra);

        return $package;
    }

    /**
     * @return Composer[][]
     */
    public function composerProvider(): array
    {
        return [
            [
                $this->createComposer(
                    $this->createRootPackage([])
                )
            ],
            [
                $this->createComposer(
                    $this->createRootPackage(
                        [
                            'dependency-guard' => [
                                'exclude' => []
                            ]
                        ]
                    )
                )
            ],
            [
                $this->createComposer(
                    $this->createRootPackage(
                        [
                            'dependency-guard' => [
                                'exclude' => [
                                    // Exact match.
                                    __CLASS__,
                                    // Namespace match.
                                    sprintf('%s\\', __NAMESPACE__),
                                    // Pattern match.
                                    sprintf('%s\\*', __NAMESPACE__),
                                ]
                            ]
                        ]
                    )
                )
            ]
        ];
    }
}
