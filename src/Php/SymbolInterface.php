<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Php;

use JsonSerializable;

interface SymbolInterface extends JsonSerializable
{
    /**
     * Get the name of the symbol.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get the path of the file in which the symbol is encountered.
     *
     * @return string
     */
    public function getFile(): string;

    /**
     * Get the line on which the symbol is encountered.
     *
     * @return int
     */
    public function getLine(): int;

    /**
     * Convert the symbol to a string.
     *
     * @return string
     */
    public function __toString(): string;
}
