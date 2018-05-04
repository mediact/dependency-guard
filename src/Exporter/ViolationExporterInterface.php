<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Exporter;

use Mediact\DependencyGuard\ViolationIteratorInterface;

interface ViolationExporterInterface
{
    /**
     * Export the given violations.
     *
     * @param ViolationIteratorInterface $violations
     *
     * @return void
     */
    public function export(ViolationIteratorInterface $violations): void;
}
