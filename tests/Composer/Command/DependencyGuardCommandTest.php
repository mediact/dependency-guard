<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Tests\Composer\Command;

use Composer\Composer;
use Composer\Config;
use Composer\EventDispatcher\EventDispatcher;
use Mediact\DependencyGuard\Composer\Command\Exporter\ViolationExporterFactoryInterface;
use Mediact\DependencyGuard\DependencyGuardFactoryInterface;
use Mediact\DependencyGuard\DependencyGuardInterface;
use Mediact\DependencyGuard\Exporter\ViolationExporterInterface;
use Mediact\DependencyGuard\Violation\ViolationIteratorInterface;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Mediact\DependencyGuard\Composer\Command\DependencyGuardCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @coversDefaultClass \Mediact\DependencyGuard\Composer\Command\DependencyGuardCommand
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DependencyGuardCommandTest extends TestCase
{
    /**
     * @return void
     *
     * @covers ::__construct
     * @covers ::configure
     */
    public function testConstructor(): void
    {
        $this->assertInstanceOf(
            DependencyGuardCommand::class,
            new DependencyGuardCommand()
        );

        $this->assertInstanceOf(
            DependencyGuardCommand::class,
            new DependencyGuardCommand(
                $this->createMock(DependencyGuardFactoryInterface::class),
                $this->createMock(ViolationExporterFactoryInterface::class)
            )
        );
    }

    /**
     * @param string $vendorDir
     *
     * @return Composer
     */
    private function createComposer(string $vendorDir): Composer
    {
        /** @var Composer|MockObject $composer */
        $composer = $this->createMock(Composer::class);

        $config = $this->createMock(Config::class);

        $composer
            ->expects(self::any())
            ->method('getConfig')
            ->willReturn($config);

        $composer
            ->expects(self::any())
            ->method('getEventDispatcher')
            ->willReturn(
                $this->createMock(EventDispatcher::class)
            );

        $config
            ->expects(self::any())
            ->method('get')
            ->with('vendor-dir', 0)
            ->willReturn($vendorDir);

        return $composer;
    }

    /**
     * @dataProvider executeProvider
     *
     * @param ViolationIteratorInterface $violations
     * @param int                        $exitCode
     *
     * @return void
     *
     * @covers ::execute
     * @covers ::registerAutoloader
     */
    public function testExecute(
        ViolationIteratorInterface $violations,
        int $exitCode
    ): void {
        /** @var DependencyGuardFactoryInterface|MockObject $guardFactory */
        $guardFactory = $this->createMock(DependencyGuardFactoryInterface::class);

        /** @var ViolationExporterFactoryInterface|MockObject $exporterFactory */
        $exporterFactory = $this->createMock(ViolationExporterFactoryInterface::class);

        $subject = new DependencyGuardCommand($guardFactory, $exporterFactory);

        $filesystem = vfsStream::setup(
            sha1(__METHOD__),
            null,
            [
                'autoload.php' => '<?php return;'
            ]
        );

        $subject->setComposer(
            $this->createComposer(
                $filesystem->url()
            )
        );

        /** @var DependencyGuardInterface|MockObject $guard */
        $guard = $this->createMock(DependencyGuardInterface::class);

        $guardFactory
            ->expects(self::once())
            ->method('create')
            ->willReturn($guard);

        $guard
            ->expects(self::once())
            ->method('determineViolations')
            ->with(self::isInstanceOf(Composer::class))
            ->willReturn($violations);

        /** @var ViolationExporterInterface|MockObject $exporter */
        $exporter = $this->createMock(ViolationExporterInterface::class);

        $exporterFactory
            ->expects(self::once())
            ->method('create')
            ->with(
                self::isInstanceOf(InputInterface::class),
                self::isInstanceOf(OutputInterface::class)
            )
            ->willReturn($exporter);

        $exporter
            ->expects(self::once())
            ->method('export')
            ->with(
                self::isInstanceOf(ViolationIteratorInterface::class)
            );

        $this->assertEquals(
            $exitCode,
            $subject->run(
                $this->createMock(InputInterface::class),
                $this->createMock(OutputInterface::class)
            )
        );
    }

    /**
     * @param int $numViolations
     *
     * @return ViolationIteratorInterface
     */
    private function createViolations(
        int $numViolations
    ): ViolationIteratorInterface {
        /** @var ViolationIteratorInterface|MockObject $violations */
        $violations = $this->createMock(ViolationIteratorInterface::class);

        $violations
            ->expects(self::any())
            ->method('count')
            ->willReturn($numViolations);

        return $violations;
    }

    /**
     * @return ViolationIteratorInterface[][]|int[][]
     */
    public function executeProvider(): array
    {
        return [
            [
                $this->createViolations(0),
                DependencyGuardCommand::EXIT_NO_VIOLATIONS
            ],
            [
                $this->createViolations(1),
                DependencyGuardCommand::EXIT_VIOLATIONS
            ],
            [
                $this->createViolations(10),
                DependencyGuardCommand::EXIT_VIOLATIONS
            ]
        ];
    }
}
