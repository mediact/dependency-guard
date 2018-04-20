<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Php;

use PhpParser\Node\Name;
use SplFileInfo;

class Symbol implements SymbolInterface
{
    /** @var SplFileInfo */
    private $file;

    /** @var Name */
    private $node;

    /**
     * Constructor.
     *
     * @param SplFileInfo $file
     * @param Name        $node
     */
    public function __construct(SplFileInfo $file, Name $node)
    {
        $this->file = $file;
        $this->node = $node;
    }

    /**
     * Get the name of the symbol.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->node->__toString();
    }

    /**
     * Get the path of the file in which the symbol is encountered.
     *
     * @return string
     */
    public function getFile(): string
    {
        return $this->file->getRealPath();
    }

    /**
     * Get the line on which the symbol is encountered.
     *
     * @return int
     */
    public function getLine(): int
    {
        return $this->node->getLine();
    }
}
