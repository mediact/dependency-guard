<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Tests\Php;

use PhpParser\Node\Name;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Mediact\DependencyGuard\Php\Symbol;
use SplFileInfo;

/**
 * @coversDefaultClass \Mediact\DependencyGuard\Php\Symbol
 */
class SymbolTest extends TestCase
{
    /**
     * @param string $name
     * @param int    $line
     *
     * @return Name
     */
    private function createName(string $name = '', int $line = 0): Name
    {
        /** @var Name|MockObject $node */
        $node = $this->createMock(Name::class);

        $node
            ->expects(self::any())
            ->method('__toString')
            ->willReturn($name);

        $node
            ->expects(self::any())
            ->method('getLine')
            ->willReturn($line);

        return $node;
    }

    /**
     * @param string $path
     *
     * @return SplFileInfo
     */
    private function createFile(string $path = ''): SplFileInfo
    {
        /** @var SplFileInfo|MockObject $file */
        $file = $this->createMock(SplFileInfo::class);

        $file
            ->expects(self::any())
            ->method('getRealPath')
            ->willReturn($path);

        return $file;
    }

    /**
     * @return void
     *
     * @covers ::__construct
     */
    public function testConstructor(): void
    {
        $this->assertInstanceOf(
            Symbol::class,
            new Symbol(
                $this->createFile(),
                $this->createName()
            )
        );
    }

    /**
     * @return Name[][]|string[][]
     */
    public function nameProvider(): array
    {
        return [
            [$this->createName('foo'), 'foo']
        ];
    }

    /**
     * @dataProvider nameProvider
     *
     * @param Name   $name
     * @param string $expected
     *
     * @return void
     *
     * @covers ::getName
     */
    public function testGetName(Name $name, string $expected): void
    {
        $subject = new Symbol(
            $this->createFile(),
            $name
        );

        $this->assertEquals($expected, $subject->getName());
    }

    /**
     * @dataProvider nameProvider
     *
     * @param Name   $name
     * @param string $expected
     *
     * @return void
     *
     * @covers ::__toString
     */
    public function testToString(Name $name, string $expected): void
    {
        $subject = new Symbol(
            $this->createFile(),
            $name
        );

        $this->assertEquals($expected, $subject->__toString());
    }

    /**
     * @return Name[][]|string[][]
     */
    public function lineProvider(): array
    {
        return [
            [$this->createName('foo', 12), 12]
        ];
    }

    /**
     * @dataProvider lineProvider
     *
     * @param Name $name
     * @param int  $expected
     *
     * @return void
     *
     * @covers ::getLine
     */
    public function testGetLine(Name $name, int $expected): void
    {
        $subject = new Symbol(
            $this->createFile(),
            $name
        );

        $this->assertEquals($expected, $subject->getLine());
    }

    /**
     * @return SplFileInfo[][]|string[][]
     */
    public function fileProvider(): array
    {
        return [
            [$this->createFile('foo'), 'foo']
        ];
    }

    /**
     * @dataProvider fileProvider
     *
     * @param SplFileInfo $file
     * @param string      $expected
     *
     * @return void
     *
     * @covers ::getFile
     */
    public function testGetFile(SplFileInfo $file, string $expected): void
    {
        $subject = new Symbol($file, $this->createName());

        $this->assertEquals($expected, $subject->getFile());
    }

    /**
     * @return SplFileInfo[][]|Name[][]|array[][]
     */
    public function jsonSerializeProvider(): array
    {
        return [
            [
                $this->createFile('foo'),
                $this->createName('bar', 42),
                [
                    'name' => 'bar',
                    'file' => 'foo',
                    'line' => 42
                ]
            ]
        ];
    }

    /**
     * @dataProvider jsonSerializeProvider
     *
     * @param SplFileInfo $file
     * @param Name        $name
     * @param array       $expected
     *
     * @return void
     *
     * @covers ::jsonSerialize
     */
    public function testJsonSerialize(
        SplFileInfo $file,
        Name $name,
        array $expected
    ): void {
        $subject = new Symbol($file, $name);

        $this->assertEquals($expected, $subject->jsonSerialize());
    }
}
