<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Reflection;

use Roave\BetterReflection\BetterReflection;
use Roave\BetterReflection\Reflection\ReflectionClass;

trait ReflectionTrait
{
    /** @var BetterReflection */
    private static $reflectionEnvironment;

    /**
     * Get the current reflection environment.
     *
     * @return BetterReflection
     */
    private static function getReflectionEnvironment(): BetterReflection
    {
        if (self::$reflectionEnvironment === null) {
            self::$reflectionEnvironment = new BetterReflection();
        }

        return self::$reflectionEnvironment;
    }

    /**
     * Get the reflection for the given class name.
     *
     * @param string $className
     *
     * @return ReflectionClass
     */
    public function getClassReflection(string $className): ReflectionClass
    {
        return static::getReflectionEnvironment()
            ->classReflector()
            ->reflect($className);
    }
}
