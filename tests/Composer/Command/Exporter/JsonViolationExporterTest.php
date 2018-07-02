<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Tests\Composer\Command\Exporter;

use Mediact\DependencyGuard\Violation\ViolationIteratorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Mediact\DependencyGuard\Composer\Command\Exporter\JsonViolationExporter;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @coversDefaultClass \Mediact\DependencyGuard\Composer\Command\Exporter\JsonViolationExporter
 */
class JsonViolationExporterTest extends TestCase
{
    /**
     * @return void
     *
     * @covers ::__construct
     */
    public function testConstructor(): void
    {
        $this->assertInstanceOf(
            JsonViolationExporter::class,
            new JsonViolationExporter(
                $this->createMock(SymfonyStyle::class)
            )
        );
    }

    /**
     * @return void
     *
     * @covers ::export
     */
    public function testExport(): void
    {
        /** @var SymfonyStyle|MockObject $prompt */
        $prompt = $this->createMock(SymfonyStyle::class);

        $subject = new JsonViolationExporter($prompt);

        $prompt
            ->expects(self::once())
            ->method('writeln')
            ->with(self::isType('string'));

        $subject->export(
            $this->createMock(ViolationIteratorInterface::class)
        );
    }
}
