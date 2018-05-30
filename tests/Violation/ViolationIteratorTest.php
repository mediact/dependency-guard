<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Tests\Violation;

use Mediact\DependencyGuard\Violation\ViolationInterface;
use PHPUnit\Framework\TestCase;
use Mediact\DependencyGuard\Violation\ViolationIterator;

/**
 * @coversDefaultClass \Mediact\DependencyGuard\Violation\ViolationIterator
 */
class ViolationIteratorTest extends TestCase
{
    /**
     * @dataProvider violationProvider
     *
     * @param ViolationInterface[] ...$violations
     *
     * @return void
     *
     * @covers ::__construct
     * @covers ::current
     * @covers ::count
     * @covers ::jsonSerialize
     */
    public function testIterator(ViolationInterface ...$violations): void
    {
        $iterator = new ViolationIterator(...$violations);

        $this->assertInstanceOf(ViolationIterator::class, $iterator);
        $this->assertCount(count($violations), $iterator);
        $this->assertEquals($violations, $iterator->jsonSerialize());

        foreach ($iterator as $violation) {
            $this->assertInstanceOf(ViolationInterface::class, $violation);
        }
    }

    /**
     * @return ViolationInterface[][]
     */
    public function violationProvider(): array
    {
        return [
            [],
            [$this->createMock(ViolationInterface::class)],
            [
                $this->createMock(ViolationInterface::class),
                $this->createMock(ViolationInterface::class),
                $this->createMock(ViolationInterface::class)
            ]
        ];
    }
}
