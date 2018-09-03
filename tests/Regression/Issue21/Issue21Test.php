<?php

namespace Mediact\DependencyGuard\Tests\Regression\Issue21;

use Composer\Util\Filesystem;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

/**
 * @see https://github.com/mediact/dependency-guard/issues/21
 */
class Issue21Test extends TestCase
{
    /**
     * @return void
     */
    public function setUp(): void
    {
        $process = new Process(
            [
                trim(`which composer` ?? `which composer.phar`),
                'install',
                '--quiet',
                '--working-dir',
                __DIR__
            ]
        );

        if ($process->run() !== 0) {
            self::markTestSkipped(
                $process->getErrorOutput()
            );
        }
    }

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

    /**
     * @return void
     */
    public function tearDown(): void
    {
        $filesystem = new Filesystem();
        $filesystem->removeDirectory(
            __DIR__ . DIRECTORY_SEPARATOR . 'vendor'
        );
    }
}
