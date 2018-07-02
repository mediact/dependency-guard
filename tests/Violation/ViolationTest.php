<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Tests\Violation;

use Composer\Package\PackageInterface;
use Mediact\DependencyGuard\Candidate\CandidateInterface;
use Mediact\DependencyGuard\Php\SymbolIteratorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Mediact\DependencyGuard\Violation\Violation;

/**
 * @coversDefaultClass \Mediact\DependencyGuard\Violation\Violation
 */
class ViolationTest extends TestCase
{
    private const MESSAGE = __FILE__;

    /**
     * @return void
     *
     * @covers ::__construct
     */
    public function testConstructor(): void
    {
        $this->assertInstanceOf(
            Violation::class,
            new Violation(
                static::MESSAGE,
                $this->createMock(CandidateInterface::class)
            )
        );
    }

    /**
     * @return void
     *
     * @covers ::getMessage
     */
    public function testGetMessage(): void
    {
        $subject = new Violation(
            static::MESSAGE,
            $this->createMock(CandidateInterface::class)
        );

        $this->assertEquals(static::MESSAGE, $subject->getMessage());
    }

    /**
     * @return void
     *
     * @covers ::getPackage
     */
    public function testGetPackage(): void
    {
        $subject = new Violation(
            static::MESSAGE,
            $this->createMock(CandidateInterface::class)
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
        $subject = new Violation(
            static::MESSAGE,
            $this->createMock(CandidateInterface::class)
        );

        $this->assertInstanceOf(
            SymbolIteratorInterface::class,
            $subject->getSymbols()
        );
    }

    /**
     * @dataProvider jsonSerializeProvider
     *
     * @param array              $expected
     * @param string             $message
     * @param CandidateInterface $result
     *
     * @return void
     *
     * @covers ::jsonSerialize
     */
    public function testJsonSerialize(
        array $expected,
        string $message,
        CandidateInterface $result
    ): void {
        $subject = new Violation($message, $result);

        $this->assertEquals(
            $expected,
            json_decode(
                json_encode($subject),
                true
            )
        );
    }

    /**
     * @return array[][]|string[][]|CandidateInterface[][]
     */
    public function jsonSerializeProvider(): array
    {
        return [
            [
                [
                    'message' => static::MESSAGE,
                    'package' => 'composer/composer',
                    'symbols' => []
                ],
                static::MESSAGE,
                $this->createCandidate(
                    $this->createPackage('composer/composer'),
                    $this->createSerializableSymbols([])
                )
            ]
        ];
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
     * @param PackageInterface        $package
     * @param SymbolIteratorInterface $symbols
     *
     * @return CandidateInterface
     */
    private function createCandidate(
        PackageInterface $package,
        SymbolIteratorInterface $symbols
    ): CandidateInterface {
        /** @var CandidateInterface|MockObject $candidate */
        $candidate = $this->createMock(CandidateInterface::class);

        $candidate
            ->expects(self::any())
            ->method('getPackage')
            ->willReturn($package);

        $candidate
            ->expects(self::any())
            ->method('getSymbols')
            ->willReturn($symbols);

        return $candidate;
    }

    /**
     * @param array $symbols
     *
     * @return SymbolIteratorInterface
     */
    private function createSerializableSymbols(
        array $symbols
    ): SymbolIteratorInterface {
        /** @var SymbolIteratorInterface|MockObject $iterator */
        $iterator = $this->createMock(SymbolIteratorInterface::class);

        $iterator
            ->expects(self::any())
            ->method('jsonSerialize')
            ->willReturn($symbols);

        return $iterator;
    }
}
