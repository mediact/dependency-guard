<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Tests\Functional\Composer\Locker;

use Composer\Factory;
use Composer\IO\NullIO;
use Composer\Package\Locker;
use Composer\Package\PackageInterface;
use Mediact\DependencyGuard\Composer\Locker\PackageRequirementsResolver;
use PHPUnit\Framework\TestCase;

/**
 * @coversNothing
 */
class PackageRequirementsResolverTest extends TestCase
{
    /**
     * @dataProvider dependentsProvider
     *
     * @param Locker $locker
     * @param string $package
     * @param string ...$dependents
     *
     * @return void
     *
     * @coversNothing
     */
    public function testDependentsArePresent(
        Locker $locker,
        string $package,
        string ...$dependents
    ): void {
        $resolver = new PackageRequirementsResolver();
        $result   = $resolver->getDependents($package, $locker);
        $packages = array_map(
            function (PackageInterface $package) : string {
                return $package->getName();
            },
            $result
        );

        foreach ($dependents as $dependent) {
            $this->assertContains($dependent, $packages);
        }
    }

    /**
     * @return Locker
     */
    private function createLocker(): Locker
    {
        putenv(
            sprintf(
                'COMPOSER=%s',
                realpath(__DIR__ . '/composer.json') ?: ''
            )
        );

        return Factory::create(new NullIO())->getLocker();
    }

    /**
     * @return array
     */
    public function dependentsProvider(): array
    {
        $locker = $this->createLocker();

        return [
            [
                $locker,
                'zendframework/zend-filter',
                'magento/framework',
                'magento/module-authorization',
                'magento/module-backend',
                'magento/module-backup',
                'magento/module-bundle',
                'magento/module-catalog',
                'magento/module-catalog-import-export',
                'magento/module-catalog-inventory',
                'magento/module-catalog-rule',
                'magento/module-catalog-url-rewrite',
                'magento/module-checkout',
                'magento/module-cms',
                'magento/module-cms-url-rewrite',
                'magento/module-config',
                'magento/module-contact',
                'magento/module-cron',
                'magento/module-customer',
                'magento/module-deploy',
                'magento/module-developer',
                'magento/module-directory',
                'magento/module-downloadable',
                'magento/module-eav',
                'magento/module-email',
                'magento/module-gift-message',
                'magento/module-grouped-product',
                'magento/module-import-export',
                'magento/module-indexer',
                'magento/module-integration',
                'magento/module-media-storage',
                'magento/module-msrp',
                'magento/module-newsletter',
                'magento/module-page-cache',
                'magento/module-payment',
                'magento/module-product-alert',
                'magento/module-quote',
                'magento/module-reports',
                'magento/module-require-js',
                'magento/module-review',
                'magento/module-rss',
                'magento/module-rule',
                'magento/module-sales',
                'magento/module-sales-rule',
                'magento/module-sales-sequence',
                'magento/module-security',
                'magento/module-shipping',
                'magento/module-store',
                'magento/module-tax',
                'magento/module-theme',
                'magento/module-translation',
                'magento/module-ui',
                'magento/module-url-rewrite',
                'magento/module-user',
                'magento/module-variable',
                'magento/module-widget',
                'magento/module-wishlist',
                'zendframework/zend-form',
                'zendframework/zend-inputfilter',
                'zendframework/zend-mvc'
            ]
        ];
    }

    /**
     * @dataProvider invalidDependentsProvider
     *
     * @param Locker $locker
     * @param string $package
     * @param string ...$invalidDependents
     *
     * @return void
     *
     * @coversNothing
     */
    public function testDependentsAreNotPresent(
        Locker $locker,
        string $package,
        string ...$invalidDependents
    ): void {
        $resolver = new PackageRequirementsResolver();
        $result   = $resolver->getDependents($package, $locker);
        $packages = array_map(
            function (PackageInterface $package) : string {
                return $package->getName();
            },
            $result
        );

        foreach ($invalidDependents as $dependent) {
            $this->assertNotContains($dependent, $packages);
        }
    }

    /**
     * @return array
     */
    public function invalidDependentsProvider(): array
    {
        $locker = $this->createLocker();

        return [
            [
                $locker,
                'zendframework/zend-filter',
                'colinmollenhour/credis',
                'colinmollenhour/php-redis-session-abstract',
                'composer/ca-bundle',
                'composer/composer',
                'composer/semver',
                'composer/spdx-licenses',
                'composer/xdebug-handler',
                'container-interop/container-interop',
                'justinrainbow/json-schema',
                'jyxon/gdpr-cookie-compliance',
                'magento/zendframework1',
                'monolog/monolog',
                'oyejorge/less.php',
                'psr/container',
                'psr/http-message',
                'psr/log',
                'seld/jsonlint',
                'seld/phar-utils',
                'symfony/console',
                'symfony/debug',
                'symfony/filesystem',
                'symfony/finder',
                'symfony/polyfill-ctype',
                'symfony/polyfill-mbstring',
                'symfony/process',
                'tedivm/jshrink',
                'zendframework/zend-code',
                'zendframework/zend-console',
                'zendframework/zend-crypt',
                'zendframework/zend-diactoros',
                'zendframework/zend-escaper',
                'zendframework/zend-eventmanager',
                'zendframework/zend-filter',
                'zendframework/zend-http',
                'zendframework/zend-hydrator',
                'zendframework/zend-loader',
                'zendframework/zend-math',
                'zendframework/zend-psr7bridge',
                'zendframework/zend-servicemanager',
                'zendframework/zend-stdlib',
                'zendframework/zend-uri',
                'zendframework/zend-validator'
            ]
        ];
    }
}
