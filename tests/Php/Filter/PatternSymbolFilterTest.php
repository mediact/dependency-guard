<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Tests\Php\Filter;

use PHPUnit\Framework\TestCase;
use Mediact\DependencyGuard\Php\Filter\PatternSymbolFilter;

/**
 * @coversDefaultClass \Mediact\DependencyGuard\Php\Filter\PatternSymbolFilter
 */
class PatternSymbolFilterTest extends TestCase
{
    /**
     * @return void
     *
     * @covers ::__construct
     */
    public function testConstructor(): void
    {
        $this->assertInstanceOf(
            PatternSymbolFilter::class,
            new PatternSymbolFilter('')
        );
    }

    /**
     * @dataProvider filterProvider
     *
     * @param string $pattern
     * @param string $symbol
     * @param bool   $expected
     *
     * @return void
     *
     * @covers ::__invoke
     */
    public function testInvoke(
        string $pattern,
        string $symbol,
        bool $expected
    ): void {
        $subject = new PatternSymbolFilter($pattern);
        $this->assertEquals($expected, $subject->__invoke($symbol));
    }

    /**
     * @return string[][]|bool[][]
     */
    public function filterProvider(): array
    {
        return [
            ['', __CLASS__, true],
            ['Foo', __CLASS__, true],
            [__CLASS__, __NAMESPACE__, true],
            [__NAMESPACE__, __CLASS__, true],
            ['*', __CLASS__, false],
            [sprintf('%s\*', __NAMESPACE__), __CLASS__, false],
            [sprintf('%s\*', __NAMESPACE__), __NAMESPACE__, true]
        ];
    }
}
