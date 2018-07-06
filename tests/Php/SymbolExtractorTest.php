<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Tests\Php;

use Mediact\DependencyGuard\Iterator\FileIteratorInterface;
use Mediact\DependencyGuard\Php\Filter\SymbolFilterInterface;
use Mediact\DependencyGuard\Php\SymbolIteratorInterface;
use PhpParser\Error;
use PhpParser\Node\Name;
use PhpParser\Parser;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Mediact\DependencyGuard\Php\SymbolExtractor;
use SplFileInfo;
use SplFileObject;

/**
 * @coversDefaultClass \Mediact\DependencyGuard\Php\SymbolExtractor
 */
class SymbolExtractorTest extends TestCase
{
    /**
     * @return void
     *
     * @covers ::__construct
     */
    public function testConstructor(): void
    {
        $this->assertInstanceOf(
            SymbolExtractor::class,
            new SymbolExtractor()
        );

        $this->assertInstanceOf(
            SymbolExtractor::class,
            new SymbolExtractor(
                $this->createMock(Parser::class)
            )
        );
    }

    /**
     * @dataProvider emptyProvider
     * @dataProvider emptyFilesProvider
     * @dataProvider filledFilesProvider
     *
     * @param Parser                $parser
     * @param FileIteratorInterface $files
     * @param int                   $expected
     *
     * @return void
     *
     * @covers ::extract
     */
    public function testExtract(
        Parser $parser,
        FileIteratorInterface $files,
        int $expected = 0
    ): void {
        $filter  = $this->createMock(SymbolFilterInterface::class);
        $subject = new SymbolExtractor($parser);

        $filter
            ->expects(self::any())
            ->method('__invoke')
            ->with(self::isType('string'))
            ->willReturn(true);

        $symbols = $subject->extract($files, $filter);

        $this->assertInstanceOf(SymbolIteratorInterface::class, $symbols);
        $this->assertCount($expected, iterator_to_array($symbols));
    }

    /**
     * @param SplFileInfo ...$files
     *
     * @return FileIteratorInterface
     */
    private function createFileIterator(
        SplFileInfo ...$files
    ): FileIteratorInterface {
        /** @var FileIteratorInterface|MockObject $iterator */
        $iterator = $this->createMock(FileIteratorInterface::class);
        $valid    = array_fill(0, count($files), true);
        $valid[]  = false;

        $iterator
            ->expects(self::any())
            ->method('valid')
            ->willReturn(...$valid);

        $iterator
            ->expects(self::any())
            ->method('current')
            ->willReturnOnConsecutiveCalls(...$files);

        return $iterator;
    }

    /**
     * @return Parser[][]|FileIteratorInterface[][]
     */
    public function emptyProvider(): array
    {
        $parser = $this->createMock(Parser::class);

        $parser
            ->expects(self::never())
            ->method('parse')
            ->with(self::anything());

        return [
            [$parser, $this->createFileIterator()]
        ];
    }

    /**
     * @param string|null $content
     *
     * @return SplFileInfo
     */
    private function createFile(string $content = null): SplFileInfo
    {
        /** @var SplFileInfo|MockObject $fileInfo */
        $fileInfo = $this->createMock(SplFileInfo::class);

        $fileInfo
            ->expects(self::any())
            ->method('isFile')
            ->willReturn(true);

        $fileInfo
            ->expects(self::any())
            ->method('isReadable')
            ->willReturn($content !== null);

        $tempFile = tempnam(sys_get_temp_dir(), "");
        file_put_contents($tempFile, $content);

        $fileInfo
            ->expects(self::any())
            ->method('__toString')
            ->willReturn($tempFile);

        return $fileInfo;
    }

    /**
     * @return Parser[][]|FileIteratorInterface[][]
     */
    public function emptyFilesProvider(): array
    {
        $parser = $this->createMock(Parser::class);

        $parser
            ->expects(self::exactly(3))
            ->method('parse')
            ->with(self::isType('string'))
            ->willThrowException(
                $this->createMock(Error::class)
            );

        return [
            [
                $parser,
                $this->createFileIterator(
                    $this->createFile(''),
                    $this->createFile(''),
                    $this->createFile('')
                )
            ]
        ];
    }

    /**
     * @return Parser[][]|FileIteratorInterface[][]
     */
    public function filledFilesProvider(): array
    {
        $parser = $this->createMock(Parser::class);
        $node   = $this->createMock(Name::class);

        $parser
            ->expects(self::exactly(1))
            ->method('parse')
            ->with(self::isType('string'))
            ->willReturn([$node, $node, $node]);

        $node
            ->expects(self::any())
            ->method('getSubNodeNames')
            ->willReturn(['name']);

        $node
            ->expects(self::any())
            ->method('toString')
            ->willReturn(__CLASS__);

        return [
            [
                $parser,
                $this->createFileIterator(
                    $this->createFile(
                        sprintf(
                            '<?php use %s;',
                            __CLASS__
                        )
                    )
                ),
                3
            ]
        ];
    }
}
