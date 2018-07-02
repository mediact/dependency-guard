<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Tests\GrumPHP;

use Composer\Composer;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use Mediact\DependencyGuard\DependencyGuardFactoryInterface;
use Mediact\DependencyGuard\DependencyGuardInterface;
use Mediact\DependencyGuard\Exporter\ViolationExporterInterface;
use Mediact\DependencyGuard\Violation\ViolationIteratorInterface;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Mediact\DependencyGuard\GrumPHP\DependencyGuard;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @coversDefaultClass \Mediact\DependencyGuard\GrumPHP\DependencyGuard
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DependencyGuardTest extends TestCase
{
    /**
     * @return DependencyGuard
     *
     * @covers ::__construct
     */
    public function testConstructor(): DependencyGuard
    {
        $subject = new DependencyGuard(
            $this->createMock(Composer::class),
            $this->createMock(DependencyGuardFactoryInterface::class),
            $this->createMock(ViolationExporterInterface::class)
        );

        $this->assertInstanceOf(DependencyGuard::class, $subject);

        return $subject;
    }

    /**
     * @depends testConstructor
     *
     * @param DependencyGuard $subject
     *
     * @return void
     *
     * @covers ::getConfigurableOptions
     */
    public function testGetConfigurableOptions(
        DependencyGuard $subject
    ): void {
        $this->assertInstanceOf(
            OptionsResolver::class,
            $subject->getConfigurableOptions()
        );
    }

    /**
     * @depends testConstructor
     *
     * @param DependencyGuard $subject
     *
     * @return void
     *
     * @covers ::getName
     */
    public function testGetName(DependencyGuard $subject): void
    {
        $this->assertInternalType('string', $subject->getName());
    }

    /**
     * @dataProvider contextProvider
     *
     * @depends testConstructor
     *
     * @param ContextInterface $context
     * @param bool             $expected
     * @param DependencyGuard  $subject
     *
     * @return void
     *
     * @covers ::canRunInContext
     */
    public function testCanRunInContext(
        ContextInterface $context,
        bool $expected,
        DependencyGuard $subject
    ): void {
        $this->assertEquals($expected, $subject->canRunInContext($context));
    }

    /**
     * @return ContextInterface[][]|bool[][]
     */
    public function contextProvider(): array
    {
        return [
            [$this->createMock(ContextInterface::class), false],
            [$this->createMock(GitPreCommitContext::class), true],
            [$this->createMock(RunContext::class), true]
        ];
    }

    /**
     * @depends testConstructor
     *
     * @param DependencyGuard $subject
     *
     * @return void
     *
     * @covers ::getConfiguration
     */
    public function testGetConfiguration(DependencyGuard $subject): void
    {
        $this->assertInternalType('array', $subject->getConfiguration());
    }

    /**
     * @dataProvider guardProvider
     *
     * @param DependencyGuardInterface $guard
     * @param array                    $files
     * @param bool                     $hasViolations
     *
     * @return void
     *
     * @covers ::run
     */
    public function testRun(
        DependencyGuardInterface $guard,
        array $files,
        bool $hasViolations
    ): void {
        /** @var DependencyGuardFactoryInterface|MockObject $factory */
        $factory = $this->createMock(DependencyGuardFactoryInterface::class);

        /** @var ViolationExporterInterface|MockObject $exporter */
        $exporter = $this->createMock(ViolationExporterInterface::class);

        $filesystem = vfsStream::setup(
            sha1(__METHOD__),
            null,
            $files
        );

        $subject = new DependencyGuard(
            $this->createMock(Composer::class),
            $factory,
            $exporter,
            $filesystem->url()
        );

        $factory
            ->expects(self::any())
            ->method('create')
            ->willReturn($guard);

        $exporter
            ->expects(
                $hasViolations
                    ? self::once()
                    : self::never()
            )
            ->method('export')
            ->with(
                self::isInstanceOf(ViolationIteratorInterface::class)
            );

        $this->assertInstanceOf(
            TaskResultInterface::class,
            $subject->run(
                $this->createMock(ContextInterface::class)
            )
        );
    }

    /**
     * @param ViolationIteratorInterface $violations
     *
     * @return DependencyGuardInterface
     */
    private function createGuard(
        ViolationIteratorInterface $violations
    ): DependencyGuardInterface {
        /** @var DependencyGuardInterface|MockObject $guard */
        $guard = $this->createMock(DependencyGuardInterface::class);

        $guard
            ->expects(self::any())
            ->method('determineViolations')
            ->with(self::isInstanceOf(Composer::class))
            ->willReturn($violations);

        return $guard;
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
     * @return DependencyGuardInterface[][]|bool[][]
     */
    public function guardProvider(): array
    {
        return [
            [
                $this->createGuard(
                    $this->createViolations(1)
                ),
                [
                    'composer.json' => ''
                ],
                false
            ],
            [
                $this->createGuard(
                    $this->createViolations(1)
                ),
                [
                    'composer.lock' => '',
                ],
                false
            ],
            [
                $this->createGuard(
                    $this->createViolations(1)
                ),
                [],
                false
            ],
            [
                $this->createGuard(
                    $this->createViolations(0)
                ),
                [
                    'composer.lock' => '',
                    'composer.json' => ''
                ],
                false
            ],
            [
                $this->createGuard(
                    $this->createViolations(1)
                ),
                [
                    'composer.lock' => '',
                    'composer.json' => ''
                ],
                true
            ],
            [
                $this->createGuard(
                    $this->createViolations(3)
                ),
                [
                    'composer.lock' => '',
                    'composer.json' => ''
                ],
                true
            ]
        ];
    }
}
