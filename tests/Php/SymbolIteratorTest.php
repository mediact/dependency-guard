<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Tests\Php;

use Mediact\DependencyGuard\Php\SymbolInterface;
use PHPUnit\Framework\TestCase;
use Mediact\DependencyGuard\Php\SymbolIterator;

/**
 * @coversDefaultClass \Mediact\DependencyGuard\Php\SymbolIterator
 */
class SymbolIteratorTest extends TestCase
{
    /**
     * @dataProvider symbolProvider
     *
     * @param SymbolInterface ...$symbols
     *
     * @return void
     * @covers ::__construct
     * @covers ::current
     * @covers ::jsonSerialize
     */
    public function testIterator(SymbolInterface ...$symbols): void
    {
        $iterator = new SymbolIterator(...$symbols);
        $this->assertEquals($symbols, $iterator->jsonSerialize());
    }

    /**
     * @return SymbolInterface[][]
     */
    public function symbolProvider(): array
    {
        /** @var SymbolInterface $symbol */
        $symbol = $this->createMock(SymbolInterface::class);

        return [
            [],
            [$symbol],
            [$symbol, $symbol, $symbol]
        ];
    }
}
