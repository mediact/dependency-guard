<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Tests\Violation\Filter;

use Mediact\DependencyGuard\Violation\Filter\ViolationFilterInterface;
use Mediact\DependencyGuard\Violation\ViolationInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Mediact\DependencyGuard\Violation\Filter\ViolationFilterChain;

/**
 * @coversDefaultClass \Mediact\DependencyGuard\Violation\Filter\ViolationFilterChain
 */
class ViolationFilterChainTest extends TestCase
{
    /**
     * @dataProvider emptyProvider
     * @dataProvider matchingProvider
     * @dataProvider nonMatchingProvider
     *
     * @param ViolationInterface         $violation
     * @param bool                       $expected
     * @param ViolationFilterInterface[] ...$filters
     *
     * @return void
     *
     * @covers ::__construct
     * @covers ::__invoke
     */
    public function testInvoke(
        ViolationInterface $violation,
        bool $expected,
        ViolationFilterInterface ...$filters
    ): void {
        $subject = new ViolationFilterChain(...$filters);
        $this->assertInstanceOf(ViolationFilterChain::class, $subject);
        $this->assertEquals($expected, $subject($violation));
    }

    /**
     * @return array
     */
    public function emptyProvider(): array
    {
        return [
            [
                $this->createMock(ViolationInterface::class),
                true
            ]
        ];
    }

    /**
     * @param ViolationInterface|null $violation
     *
     * @return ViolationFilterInterface
     */
    private function createFilter(
        ViolationInterface $violation = null
    ): ViolationFilterInterface {
        /** @var ViolationFilterInterface|MockObject $filter */
        $filter = $this->createMock(ViolationFilterInterface::class);

        $filter
            ->expects(self::any())
            ->method('__invoke')
            ->with(self::isInstanceOf(ViolationInterface::class))
            ->willReturnCallback(
                function (ViolationInterface $subject) use ($violation) : bool {
                    return $violation === null || $subject !== $violation;
                }
            );

        return $filter;
    }

    /**
     * @return array
     */
    public function matchingProvider(): array
    {
        $violation = $this->createMock(ViolationInterface::class);

        return [
            [
                $violation,
                false,
                $this->createFilter($violation)
            ],
            [
                $violation,
                false,
                $this->createFilter($violation),
                $this->createFilter(),
                $this->createFilter()
            ],
            [
                $violation,
                false,
                $this->createFilter(),
                $this->createFilter($violation),
                $this->createFilter()
            ],
            [
                $violation,
                false,
                $this->createFilter(),
                $this->createFilter(),
                $this->createFilter($violation)
            ]
        ];
    }

    /**
     * @return array
     */
    public function nonMatchingProvider(): array
    {
        $violation = $this->createMock(ViolationInterface::class);

        return [
            [
                $violation,
                true,
                $this->createFilter()
            ],
            [
                $violation,
                true,
                $this->createFilter(),
                $this->createFilter()
            ],
            [
                $violation,
                true,
                $this->createFilter(),
                $this->createFilter(),
                $this->createFilter()
            ],
            [
                $violation,
                true,
                $this->createFilter(),
                $this->createFilter(),
                $this->createFilter(
                    $this->createMock(ViolationInterface::class)
                )
            ]
        ];
    }
}
