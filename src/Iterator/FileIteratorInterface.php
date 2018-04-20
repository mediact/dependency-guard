<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Iterator;

use Iterator;
use SplFileInfo;

interface FileIteratorInterface extends Iterator
{
    /**
     * Get the current file.
     *
     * @return SplFileInfo
     */
    public function current(): SplFileInfo;
}
