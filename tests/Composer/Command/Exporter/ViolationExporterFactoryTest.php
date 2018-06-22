<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Tests\Composer\Command\Exporter;

use Mediact\DependencyGuard\Exporter\ViolationExporterInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Mediact\DependencyGuard\Composer\Command\Exporter\ViolationExporterFactory;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @coversDefaultClass \Mediact\DependencyGuard\Composer\Command\Exporter\ViolationExporterFactory
 */
class ViolationExporterFactoryTest extends TestCase
{
    /**
     * @return void
     *
     * @covers ::create
     * @covers ::getOutputFormats
     */
    public function testCreate(): void
    {
        $subject = new ViolationExporterFactory();

        $formats = $subject->getOutputFormats();

        $this->assertInternalType('array', $formats);
        $this->assertArraySubset(
            [ViolationExporterFactory::DEFAULT_FORMAT],
            $formats
        );

        foreach ($formats as $format) {
            $this->assertInstanceOf(
                ViolationExporterInterface::class,
                $subject->create(
                    $this->createInput($format),
                    $this->createOutput()
                )
            );
        }

        $this->assertInstanceOf(
            ViolationExporterInterface::class,
            $subject->create(
                $this->createInput(null),
                $this->createOutput()
            )
        );
    }

    /**
     * @return OutputInterface
     */
    private function createOutput(): OutputInterface
    {
        /** @var OutputInterface|MockObject $output */
        $output = $this->createMock(OutputInterface::class);

        $output
            ->expects(self::any())
            ->method('getFormatter')
            ->willReturn(
                $this->createMock(OutputFormatterInterface::class)
            );

        return $output;
    }

    /**
     * @param null|string $format
     *
     * @return InputInterface
     */
    public function createInput(?string $format): InputInterface
    {
        /** @var InputInterface|MockObject $input */
        $input = $this->createMock(InputInterface::class);

        $input
            ->expects(self::any())
            ->method('hasOption')
            ->with('format')
            ->willReturn($format !== null);

        $input
            ->expects(self::any())
            ->method('getOption')
            ->with('format')
            ->willReturn($format);

        return $input;
    }
}
