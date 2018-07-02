<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Tests\Composer;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\Capability\Capability;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Mediact\DependencyGuard\Composer\Plugin;

/**
 * @coversDefaultClass \Mediact\DependencyGuard\Composer\Plugin
 */
class PluginTest extends TestCase
{
    /**
     * @return void
     *
     * @covers ::getCapabilities
     */
    public function testGetCapabilities(): void
    {
        $subject      = new Plugin();
        $capabilities = $subject->getCapabilities();

        $this->assertInternalType('array', $capabilities);

        foreach ($capabilities as $type => $capability) {
            $this->assertTrue(is_a($type, Capability::class, true));
            $this->assertTrue(is_a($capability, $type, true));
        }
    }

    /**
     * @return void
     *
     * @covers ::activate
     */
    public function testActivate(): void
    {
        $subject = new Plugin();

        /** @var Composer|MockObject $composer */
        $composer = $this->createMock(Composer::class);

        $composer
            ->expects(self::never())
            ->method(self::anything());

        /** @var IOInterface|MockObject $io */
        $io = $this->createMock(IOInterface::class);

        $io
            ->expects(self::never())
            ->method(self::anything());

        $subject->activate($composer, $io);
    }
}
