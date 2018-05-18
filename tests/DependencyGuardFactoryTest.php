<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Tests;

use Mediact\DependencyGuard\DependencyGuardInterface;
use PHPUnit\Framework\TestCase;
use Mediact\DependencyGuard\DependencyGuardFactory;

/**
 * @coversDefaultClass \Mediact\DependencyGuard\DependencyGuardFactory
 */
class DependencyGuardFactoryTest extends TestCase
{
    /**
     * @return void
     *
     * @covers ::create
     */
    public function testCreate(): void
    {
        $subject = new DependencyGuardFactory();

        $this->assertInstanceOf(
            DependencyGuardInterface::class,
            $subject->create()
        );
    }
}
