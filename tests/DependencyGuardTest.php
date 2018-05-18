<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Tests;

use Composer\Composer;
use Mediact\DependencyGuard\Iterator\FileIteratorFactoryInterface;
use Mediact\DependencyGuard\Php\Filter\SymbolFilterFactoryInterface;
use Mediact\DependencyGuard\Php\SymbolExtractorInterface;
use Mediact\DependencyGuard\Violation\Filter\ViolationFilterFactoryInterface;
use Mediact\DependencyGuard\Violation\Finder\ViolationFinderInterface;
use Mediact\DependencyGuard\Violation\ViolationIteratorInterface;
use PHPUnit\Framework\TestCase;
use Mediact\DependencyGuard\DependencyGuard;

/**
 * @coversDefaultClass \Mediact\DependencyGuard\DependencyGuard
 */
class DependencyGuardTest extends TestCase
{
    /**
     * @return void
     *
     * @covers ::__construct
     * @covers ::determineViolations
     */
    public function testDetermineViolations(): void
    {
        $sourceFileFactory   = $this->createMock(FileIteratorFactoryInterface::class);
        $extractor           = $this->createMock(SymbolExtractorInterface::class);
        $symbolFilterFactory = $this->createMock(SymbolFilterFactoryInterface::class);
        $finder              = $this->createMock(ViolationFinderInterface::class);
        $resultFilterFactory = $this->createMock(ViolationFilterFactoryInterface::class);

        $subject = new DependencyGuard(
            $sourceFileFactory,
            $extractor,
            $symbolFilterFactory,
            $finder,
            $resultFilterFactory
        );

        $this->assertInstanceOf(DependencyGuard::class, $subject);

        $this->assertInstanceOf(
            ViolationIteratorInterface::class,
            $subject->determineViolations(
                $this->createMock(Composer::class)
            )
        );
    }
}
