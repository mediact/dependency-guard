<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Tests\Reflection;

use PHPUnit\Framework\TestCase;
use Mediact\DependencyGuard\Reflection\ReflectionTrait;
use Roave\BetterReflection\Reflection\ReflectionClass;

/**
 * @coversDefaultClass \Mediact\DependencyGuard\Reflection\ReflectionTrait
 */
class ReflectionTraitTest extends TestCase
{
    /**
     * @return void
     * @covers ::getClassReflection
     * @covers ::getReflectionEnvironment
     */
    public function testGetClassReflection(): void
    {
        /** @var ReflectionTrait $subject */
        $subject = $this->getMockForTrait(ReflectionTrait::class);

        $this->assertInstanceOf(
            ReflectionClass::class,
            $subject->getClassReflection(__CLASS__)
        );
    }
}
