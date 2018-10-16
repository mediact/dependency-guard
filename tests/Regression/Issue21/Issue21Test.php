<?php

namespace Mediact\DependencyGuard\Tests\Regression\Issue21;

use Mediact\DependencyGuard\Tests\Regression\ComposerTestEnvironmentTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

/**
 * @see https://github.com/mediact/dependency-guard/issues/21
 */
class Issue21Test extends TestCase
{
    use ComposerTestEnvironmentTrait;

    /**
     * @return void
     * @coversNothing
     */
    public function testNoFatalsBecauseOfConflicts(): void
    {
        $process = new Process(
            [
                PHP_BINARY,
                '../../../bin/dependency-guard'
            ],
            __DIR__
        );

        $process->run();

        self::assertNotContains('Fatal error:', $process->getErrorOutput());
    }
}
