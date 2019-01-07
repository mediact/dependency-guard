<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Tests\Regression\Issue33;

use Mediact\DependencyGuard\Tests\Regression\ComposerTestEnvironmentTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

/**
 * @see https://github.com/mediact/dependency-guard/issues/33
 */
class Issue33Test extends TestCase
{
    use ComposerTestEnvironmentTrait;

    /**
     * @return void
     * @coversNothing
     */
    public function testNoTimeoutOrSegmentationFault(): void
    {
        $process = new Process(
            [
                PHP_BINARY,
                '../../../bin/dependency-guard'
            ],
            __DIR__
        );

        $process->setTimeout(5);

        $process->run();

        $this->assertNotContains(
            'Seg',
            $process->getErrorOutput(),
            'Dependency guard encountered a segmentation fault.'
        );
    }
}
