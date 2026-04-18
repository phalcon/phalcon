<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Implementation of this file has been heavily influenced by CapsulePHP.
 * Additionally, there are implementations from ioc-interop, which is a
 * Composer dependency, and from service-interop and resolver-interop. The
 * latter two are copied and re-implemented here: service-interop is not yet
 * published on Packagist, and resolver-interop requires PHP 8.4 (this project
 * targets PHP 8.1). Once both packages become available and compatible, the
 * copies will be replaced with the actual Composer dependencies.
 *
 * @link    https://github.com/capsulephp/di
 * @license https://github.com/capsulephp/di/blob/3.x/LICENSE.md
 *
 * @link    https://github.com/ioc-interop/interface
 * @license https://github.com/ioc-interop/interface/blob/1.x/LICENSE.md
 *
 * @link    https://github.com/service-interop/interface
 * @license https://github.com/service-interop/interface/blob/1.x/LICENSE.md
 *
 * @link    https://github.com/resolver-interop/interface/tree/1.x
 * @license https://github.com/resolver-interop/interface/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Bucket\Provider;

use Phalcon\Bucket\Bucket;
use Phalcon\Bucket\BucketFactory;
use Phalcon\Bucket\Provider\Cli;
use Phalcon\Cli\Dispatcher;
use Phalcon\Cli\DispatcherInterface;
use Phalcon\Cli\Router;
use Phalcon\Cli\RouterInterface;
use Phalcon\Encryption\Security;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Events\ManagerInterface as EventsManagerInterface;
use Phalcon\Filter\Filter;
use Phalcon\Filter\FilterInterface;
use Phalcon\Html\Escaper;
use Phalcon\Html\Escaper\EscaperInterface;
use Phalcon\Html\TagFactory;
use Phalcon\Mvc\Model\Manager as ModelsManager;
use Phalcon\Mvc\Model\ManagerInterface as ModelsManagerInterface;
use Phalcon\Mvc\Model\MetaData\Memory as MetadataMemory;
use Phalcon\Mvc\Model\MetaDataInterface;
use Phalcon\Mvc\Model\Transaction\ManagerInterface as TransactionManagerInterface;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Support\HelperFactory;
use Phalcon\Support\Settings;
use Phalcon\Tests\AbstractUnitTestCase;

final class CliTest extends AbstractUnitTestCase
{
    private Bucket $bucket;

    protected function setUp(): void
    {
        $this->bucket = (new BucketFactory())
            ->addProvider(new Cli())
            ->newBucket();
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketProviderCliAliasAndInterfaceReturnSameInstance(): void
    {
        $viaAlias     = $this->bucket->get('router');
        $viaInterface = $this->bucket->get(RouterInterface::class);
        $this->assertSame($viaAlias, $viaInterface);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketProviderCliAllServiceNamesResolvable(): void
    {
        $names = [
            'annotations', 'annotationsMemory', 'dispatcher', 'escaper',
            'eventsManager', 'filter', 'helper', 'modelsManager', 'modelsMetadata',
            'router', 'security', 'settings', 'storageSerializer', 'tag', 'transactionManager',
        ];

        foreach ($names as $name) {
            $this->assertTrue($this->bucket->has($name), "Service '{$name}' should be resolvable");
        }
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketProviderCliDoesNotRegisterWebOnlyServices(): void
    {
        $this->assertFalse($this->bucket->has('cookies'));
        $this->assertFalse($this->bucket->has('crypt'));
        $this->assertFalse($this->bucket->has('flash'));
        $this->assertFalse($this->bucket->has('flashSession'));
        $this->assertFalse($this->bucket->has('request'));
        $this->assertFalse($this->bucket->has('response'));
        $this->assertFalse($this->bucket->has('url'));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketProviderCliInterfacesResolvable(): void
    {
        $interfaces = [
            DispatcherInterface::class,
            EscaperInterface::class,
            EventsManagerInterface::class,
            FilterInterface::class,
            MetaDataInterface::class,
            ModelsManagerInterface::class,
            RouterInterface::class,
            TransactionManagerInterface::class,
        ];

        foreach ($interfaces as $interface) {
            $this->assertTrue($this->bucket->has($interface), "Interface '{$interface}' should be resolvable");
        }
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketProviderCliResolvesDispatcher(): void
    {
        $this->assertInstanceOf(Dispatcher::class, $this->bucket->get('dispatcher'));
        $this->assertInstanceOf(DispatcherInterface::class, $this->bucket->get(DispatcherInterface::class));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketProviderCliResolvesEscaper(): void
    {
        $this->assertInstanceOf(Escaper::class, $this->bucket->get('escaper'));
        $this->assertInstanceOf(EscaperInterface::class, $this->bucket->get(EscaperInterface::class));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketProviderCliResolvesEventsManager(): void
    {
        $this->assertInstanceOf(EventsManager::class, $this->bucket->get('eventsManager'));
        $this->assertInstanceOf(EventsManagerInterface::class, $this->bucket->get(EventsManagerInterface::class));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketProviderCliResolvesFilter(): void
    {
        $this->assertInstanceOf(Filter::class, $this->bucket->get('filter'));
        $this->assertInstanceOf(FilterInterface::class, $this->bucket->get(FilterInterface::class));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketProviderCliResolvesHelper(): void
    {
        $this->assertInstanceOf(HelperFactory::class, $this->bucket->get('helper'));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketProviderCliResolvesModelsManager(): void
    {
        $this->assertInstanceOf(ModelsManager::class, $this->bucket->get('modelsManager'));
        $this->assertInstanceOf(ModelsManagerInterface::class, $this->bucket->get(ModelsManagerInterface::class));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketProviderCliResolvesModelsMetadata(): void
    {
        $this->assertInstanceOf(MetadataMemory::class, $this->bucket->get('modelsMetadata'));
        $this->assertInstanceOf(MetaDataInterface::class, $this->bucket->get(MetaDataInterface::class));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketProviderCliResolvesRouter(): void
    {
        $this->assertInstanceOf(Router::class, $this->bucket->get('router'));
        $this->assertInstanceOf(RouterInterface::class, $this->bucket->get(RouterInterface::class));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketProviderCliResolvesSecurity(): void
    {
        $this->assertInstanceOf(Security::class, $this->bucket->get('security'));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketProviderCliResolvesSettings(): void
    {
        $this->assertInstanceOf(Settings::class, $this->bucket->get('settings'));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketProviderCliResolvesStorageSerializer(): void
    {
        $this->assertInstanceOf(SerializerFactory::class, $this->bucket->get('storageSerializer'));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketProviderCliResolvesTag(): void
    {
        $this->assertInstanceOf(TagFactory::class, $this->bucket->get('tag'));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketProviderCliRegistersTransactionManager(): void
    {
        // Resolution requires a Di container internally (hardcoded dependency).
        // Verify registration only until Bucket replaces Di as the framework container.
        $this->assertTrue($this->bucket->has('transactionManager'));
        $this->assertTrue($this->bucket->has(TransactionManagerInterface::class));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketProviderCliServicesAreShared(): void
    {
        $a = $this->bucket->get('escaper');
        $b = $this->bucket->get('escaper');
        $this->assertSame($a, $b);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketProviderCliUsesCliDispatcherNotMvc(): void
    {
        $dispatcher = $this->bucket->get('dispatcher');
        $this->assertInstanceOf(Dispatcher::class, $dispatcher);
        $this->assertNotInstanceOf(\Phalcon\Mvc\Dispatcher::class, $dispatcher);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketProviderCliUsesCliRouterNotMvc(): void
    {
        $router = $this->bucket->get('router');
        $this->assertInstanceOf(Router::class, $router);
        $this->assertNotInstanceOf(\Phalcon\Mvc\Router::class, $router);
    }
}
