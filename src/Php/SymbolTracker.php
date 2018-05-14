<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Php;

use Mediact\DependencyGuard\Php\Filter\SymbolFilterInterface;
use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\NodeVisitorAbstract;

class SymbolTracker extends NodeVisitorAbstract implements
    SymbolTrackerInterface
{
    /** @var bool[]|Name[][] */
    private $symbols = [];

    /** @var SymbolFilterInterface */
    private $filter;

    /**
     * Constructor.
     *
     * @param SymbolFilterInterface $filter
     */
    public function __construct(SymbolFilterInterface $filter)
    {
        $this->filter = $filter;
    }

    /**
     * Track the given node.
     *
     * @param Node $node
     *
     * @return void
     */
    public function enterNode(Node $node): void
    {
        $name = null;

        if ($node instanceof Name) {
            $name = $node->toString();
        }

        if ($name === null
            || (array_key_exists($name, $this->symbols)
                && $this->symbols[$name] === false
            )
        ) {
            return;
        }

        if (!$this->filter->__invoke($name)) {
            $this->symbols[$name] = false;
            return;
        }

        if (!array_key_exists($name, $this->symbols)) {
            $this->symbols[$name] = [];
        }

        $this->symbols[$name][] = $node;
    }

    /**
     * Get the symbols that are present in the tracker.
     *
     * @return iterable|Name[][]
     */
    public function getSymbols(): iterable
    {
        return array_reduce(
            array_filter($this->symbols),
            function (array $carry, array $names) : array {
                foreach ($names as $name) {
                    $carry[] = $name;
                }

                return $carry;
            },
            []
        );
    }
}
