<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Tests\Php\Filter;

use PHPUnit\Framework\TestCase;
use Mediact\DependencyGuard\Php\Filter\ExactSymbolFilter;

/**
 * @coversDefaultClass \Mediact\DependencyGuard\Php\Filter\ExactSymbolFilter
 */
class ExactSymbolFilterTest extends TestCase
{
    /**
     * @return void
     *
     * @covers ::__construct
     */
    public function testConstructor(): void
    {
        $this->assertInstanceOf(
            ExactSymbolFilter::class,
            new ExactSymbolFilter('foo')
        );
    }

    /**
     * @dataProvider filterProvider
     *
     * @param string $search
     * @param string $symbol
     * @param bool   $expected
     *
     * @return void
     *
     * @covers ::__invoke
     */
    public function testInvoke(
        string $search,
        string $symbol,
        bool $expected
    ): void {
        $subject = new ExactSymbolFilter($search);
        $this->assertEquals($expected, $subject->__invoke($symbol));
    }

    /**
     * @return string[][]|bool[][]
     */
    public function filterProvider(): array
    {
        return [
            ['foo', 'foo', false],
            ['foo', 'bar', true],
            ['foo', 'baz', true],
            ['ba', 'baz', true]
        ];
    }
}
