<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Tests\Php\Filter;

use PHPUnit\Framework\TestCase;
use Mediact\DependencyGuard\Php\Filter\NamespaceSymbolFilter;
use stdClass;

/**
 * @coversDefaultClass \Mediact\DependencyGuard\Php\Filter\NamespaceSymbolFilter
 */
class NamespaceSymbolFilterTest extends TestCase
{
    /**
     * @return void
     *
     * @covers ::__construct
     */
    public function testConstructor(): void
    {
        $this->assertInstanceOf(
            NamespaceSymbolFilter::class,
            new NamespaceSymbolFilter(__NAMESPACE__)
        );
    }

    /**
     * @dataProvider filterProvider
     *
     * @param string $namespace
     * @param string $symbol
     * @param string $expected
     *
     * @return void
     *
     * @covers ::__invoke
     */
    public function testInvoke(
        string $namespace,
        string $symbol,
        string $expected
    ): void {
        $subject = new NamespaceSymbolFilter($namespace);
        $this->assertEquals($expected, $subject->__invoke($symbol));
    }

    /**
     * @return string[][]|bool[][]
     */
    public function filterProvider(): array
    {
        return [
            [__NAMESPACE__, __CLASS__, false],
            [__NAMESPACE__, stdClass::class, true],
            [__NAMESPACE__, 'foo', true]
        ];
    }
}
