<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Tests\Php\Filter;

use PHPUnit\Framework\TestCase;
use Mediact\DependencyGuard\Php\Filter\UserDefinedSymbolFilter;
use stdClass;

/**
 * @coversDefaultClass \Mediact\DependencyGuard\Php\Filter\UserDefinedSymbolFilter
 */
class UserDefinedSymbolFilterTest extends TestCase
{
    /**
     * @dataProvider nonExistingSymbolProvider
     * @dataProvider userDefinedSymbolProvider
     * @dataProvider internalSymbolProvider
     *
     * @param string $symbol
     * @param bool   $expected
     *
     * @return void
     *
     * @covers ::__invoke
     */
    public function testInvoke(string $symbol, bool $expected): void
    {
        $subject = new UserDefinedSymbolFilter();
        $this->assertEquals($expected, $subject->__invoke($symbol));
    }

    /**
     * @return string[][]|bool[][]
     */
    public function nonExistingSymbolProvider(): array
    {
        return [
            ['This\\Class\\Does\\Not\\Exist\\At\\All\\Ever', false]
        ];
    }

    /**
     * @return string[][]|bool[][]
     */
    public function userDefinedSymbolProvider(): array
    {
        return [
            [__CLASS__, true]
        ];
    }

    /**
     * @return string[][]|bool[][]
     */
    public function internalSymbolProvider(): array
    {
        return [
            [stdClass::class, false]
        ];
    }
}
