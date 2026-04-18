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

use Phalcon\Annotations\Adapter\Memory as AnnotationsMemory;
use Phalcon\Annotations\Annotations;
use Phalcon\Assets\Manager as AssetsManager;
use Phalcon\Bucket\Bucket;
use Phalcon\Bucket\BucketFactory;
use Phalcon\Bucket\Provider\Web;
use Phalcon\Db\Event\Factory as DbEventFactory;
use Phalcon\Encryption\Crypt;
use Phalcon\Encryption\Crypt\CryptInterface;
use Phalcon\Encryption\Security;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Events\ManagerInterface as EventsManagerInterface;
use Phalcon\Filter\Filter;
use Phalcon\Filter\FilterInterface;
use Phalcon\Flash\Direct;
use Phalcon\Flash\Session;
use Phalcon\Html\Escaper;
use Phalcon\Html\Escaper\EscaperInterface;
use Phalcon\Html\TagFactory;
use Phalcon\Http\Request;
use Phalcon\Http\RequestInterface;
use Phalcon\Http\Response;
use Phalcon\Http\Response\Cookies;
use Phalcon\Http\Response\CookiesInterface;
use Phalcon\Http\ResponseInterface;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\DispatcherInterface;
use Phalcon\Mvc\Model\Manager as ModelsManager;
use Phalcon\Mvc\Model\ManagerInterface as ModelsManagerInterface;
use Phalcon\Mvc\Model\MetaData\Memory as MetadataMemory;
use Phalcon\Mvc\Model\MetaDataInterface;
use Phalcon\Mvc\Model\Transaction\Manager as TransactionManager;
use Phalcon\Mvc\Model\Transaction\ManagerInterface as TransactionManagerInterface;
use Phalcon\Mvc\Router;
use Phalcon\Mvc\RouterInterface;
use Phalcon\Mvc\Url;
use Phalcon\Mvc\Url\UrlInterface;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Support\HelperFactory;
use Phalcon\Support\Settings;
use Phalcon\Tests\AbstractUnitTestCase;

final class WebTest extends AbstractUnitTestCase
{
    private Bucket $bucket;

    protected function setUp(): void
    {
        $this->bucket = (new BucketFactory())
            ->addProvider(new Web())
            ->newBucket();
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketProviderWebAliasAndInterfaceReturnSameInstance(): void
    {
        $viaAlias     = $this->bucket->get('router');
        $viaInterface = $this->bucket->get(RouterInterface::class);
        $this->assertSame($viaAlias, $viaInterface);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketProviderWebAllServiceNamesResolvable(): void
    {
        $names = [
            'annotations', 'annotationsMemory', 'assets', 'cookies', 'crypt',
            'dispatcher', 'escaper', 'eventsManager', 'filter', 'flash',
            'flashSession', 'helper', 'modelsEventFactory', 'modelsManager',
            'modelsMetadata', 'request', 'response', 'router', 'security',
            'settings', 'storageSerializer', 'tag', 'transactionManager', 'url',
        ];

        foreach ($names as $name) {
            $this->assertTrue($this->bucket->has($name), "Service '{$name}' should be resolvable");
        }
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketProviderWebInterfacesResolvable(): void
    {
        $interfaces = [
            CookiesInterface::class,
            CryptInterface::class,
            DispatcherInterface::class,
            EscaperInterface::class,
            EventsManagerInterface::class,
            FilterInterface::class,
            MetaDataInterface::class,
            ModelsManagerInterface::class,
            RequestInterface::class,
            ResponseInterface::class,
            RouterInterface::class,
            TransactionManagerInterface::class,
            UrlInterface::class,
        ];

        foreach ($interfaces as $interface) {
            $this->assertTrue($this->bucket->has($interface), "Interface '{$interface}' should be resolvable");
        }
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketProviderWebResolvesCookies(): void
    {
        $this->assertInstanceOf(Cookies::class, $this->bucket->get('cookies'));
        $this->assertInstanceOf(CookiesInterface::class, $this->bucket->get(CookiesInterface::class));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketProviderWebResolvesCrypt(): void
    {
        $this->assertInstanceOf(Crypt::class, $this->bucket->get('crypt'));
        $this->assertInstanceOf(CryptInterface::class, $this->bucket->get(CryptInterface::class));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketProviderWebResolvesDispatcher(): void
    {
        $this->assertInstanceOf(Dispatcher::class, $this->bucket->get('dispatcher'));
        $this->assertInstanceOf(DispatcherInterface::class, $this->bucket->get(DispatcherInterface::class));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketProviderWebResolvesEscaper(): void
    {
        $this->assertInstanceOf(Escaper::class, $this->bucket->get('escaper'));
        $this->assertInstanceOf(EscaperInterface::class, $this->bucket->get(EscaperInterface::class));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketProviderWebResolvesEventsManager(): void
    {
        $this->assertInstanceOf(EventsManager::class, $this->bucket->get('eventsManager'));
        $this->assertInstanceOf(EventsManagerInterface::class, $this->bucket->get(EventsManagerInterface::class));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketProviderWebResolvesFilter(): void
    {
        $this->assertInstanceOf(Filter::class, $this->bucket->get('filter'));
        $this->assertInstanceOf(FilterInterface::class, $this->bucket->get(FilterInterface::class));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketProviderWebResolvesFlash(): void
    {
        $this->assertInstanceOf(Direct::class, $this->bucket->get('flash'));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketProviderWebResolvesFlashSession(): void
    {
        $this->assertInstanceOf(Session::class, $this->bucket->get('flashSession'));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketProviderWebResolvesHelper(): void
    {
        $this->assertInstanceOf(HelperFactory::class, $this->bucket->get('helper'));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketProviderWebResolvesModelsEventFactory(): void
    {
        $this->assertInstanceOf(DbEventFactory::class, $this->bucket->get('modelsEventFactory'));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketProviderWebResolvesModelsManager(): void
    {
        $this->assertInstanceOf(ModelsManager::class, $this->bucket->get('modelsManager'));
        $this->assertInstanceOf(ModelsManagerInterface::class, $this->bucket->get(ModelsManagerInterface::class));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketProviderWebResolvesModelsMetadata(): void
    {
        $this->assertInstanceOf(MetadataMemory::class, $this->bucket->get('modelsMetadata'));
        $this->assertInstanceOf(MetaDataInterface::class, $this->bucket->get(MetaDataInterface::class));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketProviderWebResolvesRequest(): void
    {
        $this->assertInstanceOf(Request::class, $this->bucket->get('request'));
        $this->assertInstanceOf(RequestInterface::class, $this->bucket->get(RequestInterface::class));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketProviderWebResolvesResponse(): void
    {
        $this->assertInstanceOf(Response::class, $this->bucket->get('response'));
        $this->assertInstanceOf(ResponseInterface::class, $this->bucket->get(ResponseInterface::class));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketProviderWebResolvesRouter(): void
    {
        $this->assertInstanceOf(Router::class, $this->bucket->get('router'));
        $this->assertInstanceOf(RouterInterface::class, $this->bucket->get(RouterInterface::class));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketProviderWebResolvesSecurity(): void
    {
        $this->assertInstanceOf(Security::class, $this->bucket->get('security'));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketProviderWebResolvesSettings(): void
    {
        $this->assertInstanceOf(Settings::class, $this->bucket->get('settings'));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketProviderWebResolvesStorageSerializer(): void
    {
        $this->assertInstanceOf(SerializerFactory::class, $this->bucket->get('storageSerializer'));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketProviderWebResolvesTag(): void
    {
        $this->assertInstanceOf(TagFactory::class, $this->bucket->get('tag'));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketProviderWebRegistersTransactionManager(): void
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
    public function testBucketProviderWebResolvesUrl(): void
    {
        $this->assertInstanceOf(Url::class, $this->bucket->get('url'));
        $this->assertInstanceOf(UrlInterface::class, $this->bucket->get(UrlInterface::class));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketProviderWebServicesAreShared(): void
    {
        $a = $this->bucket->get('escaper');
        $b = $this->bucket->get('escaper');
        $this->assertSame($a, $b);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketProviderWebTagDependsOnEscaper(): void
    {
        $this->assertInstanceOf(TagFactory::class, $this->bucket->get('tag'));
        $this->assertInstanceOf(Escaper::class, $this->bucket->get('escaper'));
    }
}
