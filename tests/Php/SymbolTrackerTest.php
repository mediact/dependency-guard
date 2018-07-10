<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Tests\Php;

use Mediact\DependencyGuard\Php\Filter\SymbolFilterInterface;
use PhpParser\Node;
use PhpParser\Node\Name;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Mediact\DependencyGuard\Php\SymbolTracker;

/**
 * @coversDefaultClass \Mediact\DependencyGuard\Php\SymbolTracker
 */
class SymbolTrackerTest extends TestCase
{
    /**
     * @dataProvider whitelistedNodesProvider
     * @dataProvider blacklistedNodesProvider
     *
     * @param SymbolFilterInterface $filter
     * @param Node[]                $expected
     * @param Node                  ...$nodes
     *
     * @return void
     *
     * @covers ::__construct
     * @covers ::enterNode
     * @covers ::getSymbols
     */
    public function testGetSymbols(
        SymbolFilterInterface $filter,
        array $expected,
        Node ...$nodes
    ): void {
        $subject = new SymbolTracker($filter);

        foreach ($nodes as $node) {
            $subject->enterNode($node);
        }

        $actual = iterator_to_array($subject->getSymbols());

        $this->assertSame($expected, $actual);
    }

    /**
     * @param string|null $name
     *
     * @return Node
     */
    private function createNode(string $name = null): Node
    {
        /** @var Node|MockObject $node */
        $node = $this->createMock(
            $name === null
                ? Node::class
                : Name::class
        );

        $node
            ->expects(self::any())
            ->method('toString')
            ->willReturn($name);

        return $node;
    }

    /**
     * @return SymbolFilterInterface[][]|Node[][][]|Node[][]
     */
    public function whitelistedNodesProvider(): array
    {
        $filter = $this->createMock(SymbolFilterInterface::class);

        $filter
            ->expects(self::any())
            ->method('__invoke')
            ->with(self::isType('string'))
            ->willReturn(false);

        return [
            [$filter, []],
            [
                $filter,
                [],
                $this->createNode(__CLASS__)
            ],
            [
                $filter,
                [],
                $this->createNode(__CLASS__),
                $this->createNode(__CLASS__)
            ]
        ];
    }

    /**
     * @return SymbolFilterInterface[][]|Node[][][]|Node[][]
     */
    public function blacklistedNodesProvider(): array
    {
        $filter = $this->createMock(SymbolFilterInterface::class);

        $filter
            ->expects(self::any())
            ->method('__invoke')
            ->with(self::isType('string'))
            ->willReturn(true);

        $node = $this->createNode(__CLASS__);

        return [
            [$filter, []],
            [
                $filter,
                [$node],
                $node
            ],
            [
                $filter,
                [$node, $node, $node],
                $node,
                $node,
                $node
            ]
        ];
    }
}
