<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\Prodep\Composer\Command;

use Composer\Command\BaseCommand;
use Composer\Composer;
use Mediact\Prodep\Iterator\FileIteratorFactoryInterface;
use Mediact\Prodep\Composer\Iterator\SourceFileIteratorFactory;
use Mediact\Prodep\Php\SymbolExtractor;
use Mediact\Prodep\Php\SymbolExtractorInterface;
use ReflectionClass;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CheckProductionDependencies extends BaseCommand
{
    /** @var FileIteratorFactoryInterface */
    private $sourceFileFactory;

    /** @var SymbolExtractorInterface */
    private $extractor;

    /**
     * Constructor.
     *
     * @param FileIteratorFactoryInterface|null $sourceFileFactory
     * @param SymbolExtractorInterface|null     $extractor
     */
    public function __construct(
        FileIteratorFactoryInterface $sourceFileFactory = null,
        SymbolExtractorInterface $extractor = null
    ) {
        $this->sourceFileFactory = (
            $sourceFileFactory ?? new SourceFileIteratorFactory()
        );
        $this->extractor         = $extractor ?? new SymbolExtractor();

        parent::__construct();
    }

    /**
     * Configure the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('check-production-deps');
        $this->setDescription(
            'Check Composer dependencies for a --no-dev install.'
        );
    }

    /**
     * Execute the command.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $prompt     = new SymfonyStyle($input, $output);
        $composer   = $this->getComposer(true);
        $files      = $this->sourceFileFactory->create($composer);
        $exclusions = $this->getExclusions($composer);
        $lock       = $composer->getLocker()->getLockData();

        $packages = $this->extractPackages(
            $composer->getConfig()->get('vendor-dir', 0),
            ...$this
                ->extractor
                ->extract($files, ...$exclusions)
                ->getSymbols()
        );

        $lockedPackages = array_map(
            function (array $package) : string {
                return $package['name'];
            },
            $lock['packages']
        );

        $lockedDevPackages = array_map(
            function (array $package) : string {
                return $package['name'];
            },
            $lock['packages-dev']
        );

        $numErrors = 0;

        foreach ($packages as $package => $symbols) {
            if (in_array($package, $lockedDevPackages, true)) {
                $numErrors++;

                $prompt->error(
                    sprintf(
                        'Code base is dependent on dev package %s.',
                        $package
                    )
                );
                $prompt->listing($symbols);

                continue;
            }

            if (!in_array($package, $lockedPackages, true)) {
                $numErrors++;

                $prompt->error(
                    sprintf(
                        'Package is not installed: %s.',
                        $package
                    )
                );
                $prompt->listing($symbols);
            }
        }

        return $numErrors === 0 ? 0 : 1;
    }

    /**
     * Get the exclusions from the Composer root package.
     *
     * @param Composer $composer
     *
     * @return string[]
     */
    private function getExclusions(Composer $composer): array
    {
        $extra = $composer->getPackage()->getExtra();

        return $extra['prodep']['exclude'] ?? [];
    }

    /**
     * @param string   $vendorPath
     * @param string[] ...$symbols
     *
     * @return string[]
     */
    private function extractPackages(
        string $vendorPath,
        string ...$symbols
    ): array {
        $packages = [];

        foreach ($symbols as $symbol) {
            $package = $this->extractPackage($vendorPath, $symbol);

            if ($package === null) {
                continue;
            }

            if (!array_key_exists($package, $packages)) {
                $packages[$package] = [];
            }

            $packages[$package][] = $symbol;
        }

        return $packages;
    }

    /**
     * Extract the package name from the given PHP symbol.
     *
     * @param string $vendorPath
     * @param string $symbol
     *
     * @return string|null
     */
    private function extractPackage(string $vendorPath, string $symbol): ?string
    {
        $reflection = new ReflectionClass($symbol);
        $file       = $reflection->getFileName();

        // This happens for symbols in the current package.
        if (strpos($file, $vendorPath) !== 0) {
            return null;
        }

        $structure = explode(
            DIRECTORY_SEPARATOR,
            preg_replace(
                sprintf(
                    '/^%s/',
                    preg_quote($vendorPath . DIRECTORY_SEPARATOR, '/')
                ),
                '',
                $file
            ),
            3
        );

        // This happens when other code extends Composer root code, like:
        // composer/ClassLoader.php
        if (count($structure) < 3) {
            return null;
        }

        [$vendor, $package] = $structure;

        return sprintf('%s/%s', $vendor, $package);
    }
}
