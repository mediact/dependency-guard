<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Tests\Regression;

use ReflectionObject;
use Symfony\Component\Process\Process;
use Composer\Util\Filesystem;

trait ComposerTestEnvironmentTrait
{
    /** @var string */
    private $workingDirectory;

    /**
     * @return string
     */
    private function getWorkingDirectory(): string
    {
        if ($this->workingDirectory === null) {
            $reflection = new ReflectionObject($this);

            $this->workingDirectory = dirname($reflection->getFileName());
        }

        return $this->workingDirectory;
    }

    /**
     * Create a composer process for the given command.
     *
     * @param string $command
     * @param string ...$arguments
     *
     * @return Process
     */
    protected function createComposerProcess(
        string $command,
        string ...$arguments
    ): Process {
        return new Process(
            array_merge(
                [
                    PHP_BINARY,
                    trim(`which composer` ?? `which composer.phar`),
                    $command,
                ],
                $arguments,
                [
                    '--quiet',
                    '--working-dir',
                    $this->getWorkingDirectory()
                ]
            )
        );
    }

    /**
     * @return void
     */
    public function setUp(): void
    {
        $process = $this->createComposerProcess('install');

        if ($process->run() !== 0) {
            self::markTestSkipped(
                $process->getErrorOutput()
            );
        }
    }

    /**
     * @return void
     */
    public function tearDown(): void
    {
        $filesystem = new Filesystem();
        $filesystem->removeDirectory(
            $this->getWorkingDirectory() . DIRECTORY_SEPARATOR . 'vendor'
        );
    }
}
