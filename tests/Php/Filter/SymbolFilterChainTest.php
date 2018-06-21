<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Tests\Php\Filter;

use Mediact\DependencyGuard\Php\Filter\SymbolFilterInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Mediact\DependencyGuard\Php\Filter\SymbolFilterChain;

/**
 * @coversDefaultClass \Mediact\DependencyGuard\Php\Filter\SymbolFilterChain
 */
class SymbolFilterChainTest extends TestCase
{
    /**
     * @dataProvider filterProvider
     *
     * @param string                $symbol
     * @param bool                  $expected
     * @param SymbolFilterInterface ...$filters
     *
     * @return void
     *
     * @covers ::__construct
     * @covers ::__invoke
     */
    public function testChain(
        string $symbol,
        bool $expected,
        SymbolFilterInterface ...$filters
    ): void {
        $subject = new SymbolFilterChain(...$filters);
        $this->assertEquals($expected, $subject->__invoke($symbol));
    }

    /**
     * @param bool|null $acceptInput
     *
     * @return SymbolFilterInterface
     */
    private function createFilter(?bool $acceptInput): SymbolFilterInterface
    {
        /** @var SymbolFilterInterface|MockObject $filter */
        $filter = $this->createMock(SymbolFilterInterface::class);

        $filter
            ->expects(
                $acceptInput === null
                    ? self::never()
                    : self::once()
            )
            ->method('__invoke')
            ->with(self::isType('string'))
            ->willReturn($acceptInput);

        return $filter;
    }

    /**
     * @return string[][]|bool[][]|SymbolFilterInterface[][]
     */
    public function filterProvider(): array
    {
        return [
            ['foo', true],
            [
                'foo',
                true,
                $this->createFilter(true)
            ],
            [
                'foo',
                false,
                $this->createFilter(true),
                $this->createFilter(false),
                $this->createFilter(null)
            ]
        ];
    }
}
