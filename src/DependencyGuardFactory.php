<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard;

use Mediact\DependencyGuard\Composer\Iterator\SourceFileIteratorFactory;
use Mediact\DependencyGuard\Php\Filter\SymbolFilterFactory;
use Mediact\DependencyGuard\Php\SymbolExtractor;
use Mediact\DependencyGuard\Violation\Filter\ViolationFilterFactory;
use Mediact\DependencyGuard\Violation\Finder\ViolationFinder;

class DependencyGuardFactory implements DependencyGuardFactoryInterface
{
    /**
     * Create a new dependency guard.
     *
     * @return DependencyGuardInterface
     */
    public function create(): DependencyGuardInterface
    {
        return new DependencyGuard(
            new SourceFileIteratorFactory(),
            new SymbolExtractor(),
            new SymbolFilterFactory(),
            new ViolationFinder(),
            new ViolationFilterFactory()
        );
    }
}
