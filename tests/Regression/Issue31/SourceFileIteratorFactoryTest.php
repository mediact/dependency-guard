<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Tests\Regression\Issue31;

use Composer\Autoload\AutoloadGenerator;
use Composer\Composer;
use Composer\Config;
use Composer\Installer\InstallationManager;
use Composer\Package\RootPackageInterface;
use Mediact\DependencyGuard\Iterator\FileIteratorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Mediact\DependencyGuard\Composer\Iterator\SourceFileIteratorFactory;

/**
 * @see https://github.com/mediact/dependency-guard/issues/31
 */
class SourceFileIteratorFactoryTest extends TestCase
{
    /**
     * @dataProvider emptyProvider
     * @dataProvider classmapProvider
     * @dataProvider filesProvider
     * @dataProvider namespaceProvider
     *
     * @param Composer $composer
     *
     * @return void
     *
     * @coversNothing
     */
    public function testCreate(Composer $composer): void
    {
        $subject = new SourceFileIteratorFactory();

        $this->assertInstanceOf(
            FileIteratorInterface::class,
            $subject->create($composer)
        );
    }

    /**
     * @param Config            $config
     * @param AutoloadGenerator $autoloadGenerator
     *
     * @return Composer
     */
    private function createComposer(
        Config $config,
        AutoloadGenerator $autoloadGenerator
    ): Composer {
        /** @var Composer|MockObject $composer */
        $composer = $this->createMock(Composer::class);

        $composer
            ->expects(self::any())
            ->method('getInstallationManager')
            ->willReturn(
                $this->createMock(InstallationManager::class)
            );

        $composer
            ->expects(self::any())
            ->method('getPackage')
            ->willReturn(
                $this->createMock(RootPackageInterface::class)
            );

        $composer
            ->expects(self::any())
            ->method('getConfig')
            ->willReturn($config);

        $composer
            ->expects(self::any())
            ->method('getAutoloadGenerator')
            ->willReturn($autoloadGenerator);

        return $composer;
    }

    /**
     * @param bool $authoritative
     *
     * @return Config
     */
    private function createConfig(bool $authoritative): Config
    {
        /** @var Config|MockObject $config */
        $config = $this->createMock(Config::class);

        $config
            ->expects(self::any())
            ->method('get')
            ->with('classmap-authoritative')
            ->willReturn($authoritative);

        return $config;
    }

    /**
     * @param array $directives
     *
     * @return AutoloadGenerator
     */
    public function createAutoloadGenerator(
        array $directives = []
    ): AutoloadGenerator {
        /** @var AutoloadGenerator|MockObject $generator */
        $generator = $this->createMock(AutoloadGenerator::class);

        $generator
            ->expects(self::any())
            ->method('buildPackageMap')
            ->with(
                self::isInstanceOf(InstallationManager::class),
                self::isInstanceOf(RootPackageInterface::class),
                self::isType('array')
            )
            ->willReturn([]);

        $generator
            ->expects(self::any())
            ->method('parseAutoloads')
            ->with(
                self::isType('array'),
                self::isInstanceOf(RootPackageInterface::class)
            )
            ->willReturn($directives);

        return $generator;
    }

    /**
     * @return Composer[][]
     */
    public function emptyProvider(): array
    {
        return [
            [
                $this->createComposer(
                    $this->createConfig(true),
                    $this->createAutoloadGenerator()
                )
            ],
            [
                $this->createComposer(
                    $this->createConfig(false),
                    $this->createAutoloadGenerator()
                )
            ]
        ];
    }

    /**
     * @return Composer[][]
     */
    public function filesProvider(): array
    {
        $config = $this->createConfig(true);

        return [
            [
                $this->createComposer(
                    $config,
                    $this->createAutoloadGenerator()
                )
            ],
            [
                $this->createComposer(
                    $config,
                    $this->createAutoloadGenerator(
                        [
                            'files' => [
                                // Readable file.
                                __FILE__,
                                // Not readable.
                                __CLASS__
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
    public function classmapProvider(): array
    {
        return [
            [
                $this->createComposer(
                    $this->createConfig(true),
                    $this->createAutoloadGenerator(
                        [
                            'classmap' => [
                                __NAMESPACE__ => __DIR__
                            ]
                        ]
                    )
                )
            ],
            [
                $this->createComposer(
                    $this->createConfig(false),
                    $this->createAutoloadGenerator(
                        [
                            'classmap' => [
                                __NAMESPACE__ => __DIR__
                            ]
                        ]
                    )
                )
            ],
            [
                $this->createComposer(
                    $this->createConfig(true),
                    $this->createAutoloadGenerator(
                        [
                            'classmap' => [
                                __NAMESPACE__ => __DIR__,
                                __CLASS__ => __FILE__
                            ],
                            'exclude-from-classmap' => [
                                str_replace('/', '\\', __FILE__)
                            ]
                        ]
                    )
                )
            ],
        ];
    }

    /**
     * @return Composer[][]
     */
    public function namespaceProvider(): array
    {
        $config = $this->createConfig(true);

        return [
            [
                $this->createComposer(
                    $config,
                    $this->createAutoloadGenerator(
                        [
                            'psr-0' => [
                                __NAMESPACE__ => [__DIR__],
                                __CLASS__ => [__DIR__]
                            ]
                        ]
                    )
                )
            ],
            [
                $this->createComposer(
                    $config,
                    $this->createAutoloadGenerator(
                        [
                            'psr-4' => [
                                __NAMESPACE__ => [__DIR__],
                                __CLASS__ => [__DIR__]
                            ]
                        ]
                    )
                )
            ]
        ];
    }
}
