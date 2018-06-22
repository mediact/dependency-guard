<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Tests\Composer;

use Composer\Command\BaseCommand;
use PHPUnit\Framework\TestCase;
use Mediact\DependencyGuard\Composer\CommandProvider;

/**
 * @coversDefaultClass \Mediact\DependencyGuard\Composer\CommandProvider
 */
class CommandProviderTest extends TestCase
{
    /**
     * @return void
     *
     * @covers ::getCommands
     */
    public function testGetCommands(): void
    {
        $subject  = new CommandProvider();
        $commands = $subject->getCommands();

        $this->assertInternalType('array', $commands);

        foreach ($commands as $command) {
            $this->assertInstanceOf(BaseCommand::class, $command);
        }
    }
}
