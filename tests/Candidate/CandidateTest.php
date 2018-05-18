<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Tests\Candidate;

use Composer\Package\PackageInterface;
use Mediact\DependencyGuard\Php\SymbolIteratorInterface;
use PHPUnit\Framework\TestCase;
use Mediact\DependencyGuard\Candidate\Candidate;

/**
 * @coversDefaultClass \Mediact\DependencyGuard\Candidate\Candidate
 */
class CandidateTest extends TestCase
{
    /**
     * @return void
     *
     * @covers ::__construct
     */
    public function testConstructor(): void
    {
        $this->assertInstanceOf(
            Candidate::class,
            new Candidate(
                $this->createMock(PackageInterface::class),
                $this->createMock(SymbolIteratorInterface::class)
            )
        );
    }

    /**
     * @return void
     *
     * @covers ::getPackage
     */
    public function testGetPackage(): void
    {
        $subject = new Candidate(
            $this->createMock(PackageInterface::class),
            $this->createMock(SymbolIteratorInterface::class)
        );

        $this->assertInstanceOf(
            PackageInterface::class,
            $subject->getPackage()
        );
    }

    /**
     * @return void
     *
     * @covers ::getSymbols
     */
    public function testGetSymbols(): void
    {
        $subject = new Candidate(
            $this->createMock(PackageInterface::class),
            $this->createMock(SymbolIteratorInterface::class)
        );

        $this->assertInstanceOf(
            SymbolIteratorInterface::class,
            $subject->getSymbols()
        );
    }
}
