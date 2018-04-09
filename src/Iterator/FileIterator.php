<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\Prodep\Iterator;

use IteratorIterator;
use SplFileInfo;

class FileIterator extends IteratorIterator implements FileIteratorInterface
{
    /**
     * Get the current file.
     *
     * @return SplFileInfo
     */
    public function current(): SplFileInfo
    {
        return parent::current();
    }
}
