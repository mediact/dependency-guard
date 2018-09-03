<?php

namespace Mediact\DependencyGuard\Tests\Regression\Issue21;

use PHPUnit\Framework\TestCase;

class Issue21Test extends TestCase
{
    /**
     * @return void
     */
    public function setUp(): void
    {
        exec("composer install --quiet --working-dir=".escapeshellarg(__DIR__), $output, $return);
        if ($return !== 0) {
            self::markTestSkipped(implode(PHP_EOL, $output));
        }
    }

    /**
     * @coversNothing
     * @return void
     */
    public function testNoFatalsBecauseOfConflicts(): void
    {
        self::assertNotContains(
            'Fatal error:',
            shell_exec('cd '.escapeshellarg(__DIR__).' && php ../../../bin/dependency-guard').''
        );
    }

    /**
     * @return void
     */
    public function tearDown(): void
    {
       $this->delete(__DIR__.DIRECTORY_SEPARATOR.'vendor');
    }

    /**
     * @param string $dir
     */
    private function delete(string $dir): void
    {
        foreach (array_diff(scandir($dir), ['.', '..']) as $file) {
            $file = $dir.DIRECTORY_SEPARATOR.$file;
            if (is_file($file)) {
                unlink($file);
            } elseif (is_dir($file)) {
                $this->delete($file);
            }
        }
        rmdir($dir);
    }
}
