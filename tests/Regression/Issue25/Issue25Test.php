<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Tests\Regression\Issue25;

use Mediact\DependencyGuard\Tests\Regression\ComposerTestEnvironmentTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

/**
 * @see https://github.com/mediact/dependency-guard/issues/25
 */
class Issue25Test extends TestCase
{
    use ComposerTestEnvironmentTrait;

    /**
     * @return void
     * @coversNothing
     */
    public function testNoOutOfMemoryError(): void
    {
        $process = new Process(
            [
                PHP_BINARY,
                '-d',
                'memory_limit=15M',
                '../../../bin/dependency-guard'
            ],
            __DIR__
        );

        // The exit code of the process is of no concern to the issue.
        $process->run();

        $this->assertNotContains(
            'Allowed memory size',
            $process->getErrorOutput(),
            'Dependency guard ran out of memory.'
        );
    }
}
