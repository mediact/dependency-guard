<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Tests\Regression\Issue31;

use Mediact\DependencyGuard\Php\SymbolInterface;
use Mediact\DependencyGuard\Php\SymbolIteratorInterface;
use Mediact\DependencyGuard\Violation\ViolationInterface;
use Mediact\DependencyGuard\Violation\ViolationIteratorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Mediact\DependencyGuard\Composer\Command\Exporter\TextViolationExporter;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @see https://github.com/mediact/dependency-guard/issues/31
 */
class TextViolationExporterTest extends TestCase
{
    private const WORKING_DIRECTORY = 'Q:\\Test\\';

    /**
     * @dataProvider violationProvider
     *
     * @param ViolationIteratorInterface $violations
     * @param int                        $numSuccess
     * @param int                        $numError
     *
     * @return void
     *
     * @coversNothing
     */
    public function testExport(
        ViolationIteratorInterface $violations,
        int $numSuccess,
        int $numError
    ): void {
        if (DIRECTORY_SEPARATOR !== '\\') {
            $this->markTestSkipped(
                'This can only be tested on an OS having backslash (\\) as directory separator.'
            );
            return;
        }

        /** @var SymfonyStyle|MockObject $prompt */
        $prompt  = $this->createMock(SymfonyStyle::class);
        $subject = new TextViolationExporter(
            $prompt,
            static::WORKING_DIRECTORY
        );

        $prompt
            ->expects(self::exactly($numSuccess))
            ->method('success')
            ->with('No dependency violations encountered!');

        $prompt
            ->expects(self::exactly($numError))
            ->method('error')
            ->with(self::isType('string'));

        $subject->export($violations);
    }

    /**
     * @param ViolationInterface ...$violations
     *
     * @return ViolationIteratorInterface
     */
    private function createViolations(
        ViolationInterface ...$violations
    ): ViolationIteratorInterface {
        /** @var ViolationIteratorInterface|MockObject $iterator */
        $iterator = $this->createMock(ViolationIteratorInterface::class);
        $valid    = array_fill(0, count($violations), true);
        $valid[]  = false;

        $iterator
            ->expects(self::any())
            ->method('count')
            ->willReturn(count($violations));

        $iterator
            ->expects(self::any())
            ->method('valid')
            ->willReturn(...$valid);

        $iterator
            ->expects(self::any())
            ->method('current')
            ->willReturnOnConsecutiveCalls(
                ...$violations
            );

        return $iterator;
    }

    /**
     * @param string          $message
     * @param SymbolInterface ...$symbols
     *
     * @return ViolationInterface
     */
    private function createViolation(
        string $message,
        SymbolInterface ...$symbols
    ): ViolationInterface {
        /** @var SymbolIteratorInterface|MockObject $symbolIterator */
        $symbolIterator = $this->createMock(SymbolIteratorInterface::class);
        $valid          = array_fill(0, count($symbols), true);
        $valid[]        = false;

        $symbolIterator
            ->expects(self::any())
            ->method('valid')
            ->willReturn(...$valid);

        $symbolIterator
            ->expects(self::any())
            ->method('current')
            ->willReturnOnConsecutiveCalls(...$symbols);

        /** @var ViolationInterface|MockObject $violation */
        $violation = $this->createMock(ViolationInterface::class);

        $violation
            ->expects(self::any())
            ->method('getMessage')
            ->willReturn($message);

        $violation
            ->expects(self::any())
            ->method('getSymbols')
            ->willReturn($symbolIterator);

        return $violation;
    }

    /**
     * @param string $name
     * @param string $file
     * @param int    $line
     *
     * @return SymbolInterface
     */
    private function createSymbol(
        string $name,
        string $file,
        int $line
    ): SymbolInterface {
        /** @var SymbolInterface|MockObject $symbol */
        $symbol = $this->createMock(SymbolInterface::class);

        $symbol
            ->expects(self::any())
            ->method('getName')
            ->willReturn($name);

        $symbol
            ->expects(self::any())
            ->method('getFile')
            ->willReturn(
                str_replace('/', '\\', $file)
            );

        $symbol
            ->expects(self::any())
            ->method('getLine')
            ->willReturn($line);

        return $symbol;
    }

    /**
     * @return ViolationIteratorInterface[][]|int[][]
     */
    public function violationProvider(): array
    {
        return [
            [
                $this->createViolations(),
                1,
                0
            ],
            [
                $this->createViolations(
                    $this->createViolation('foo')
                ),
                0,
                2
            ],
            [
                $this->createViolations(
                    $this->createViolation('foo'),
                    $this->createViolation(
                        'bar',
                        $this->createSymbol(
                            __CLASS__,
                            __FILE__,
                            __LINE__
                        )
                    ),
                    $this->createViolation(
                        'baz',
                        $this->createSymbol(
                            __CLASS__,
                            __FILE__,
                            __LINE__
                        ),
                        $this->createSymbol(
                            __CLASS__,
                            __FILE__,
                            __LINE__
                        ),
                        $this->createSymbol(
                            __CLASS__,
                            __FILE__,
                            __LINE__
                        )
                    )
                ),
                0,
                4
            ]
        ];
    }
}
