<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Fixtures\Traits;

use PDO;
use Phalcon\Annotations\Adapter\Memory as AnnotationsMemory;
use Phalcon\Cache\Adapter\Libmemcached as StorageLibmemcached;
use Phalcon\Cache\Adapter\Stream as StorageStream;
use Phalcon\Cache\AdapterFactory;
use Phalcon\Cli\Console;
use Phalcon\Db\Adapter\AdapterInterface;
use Phalcon\Db\Adapter\PdoFactory;
use Phalcon\Db\Profiler;
use Phalcon\Di\Di;
use Phalcon\Di\DiInterface;
use Phalcon\Di\FactoryDefault;
use Phalcon\Di\FactoryDefault\Cli as CliFactoryDefault;
use Phalcon\Encryption\Crypt;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Filter;
use Phalcon\Html\Escaper;
use Phalcon\Html\TagFactory;
use Phalcon\Http\Request;
use Phalcon\Http\Response;
use Phalcon\Mvc\Model\Manager as ModelsManager;
use Phalcon\Mvc\Model\MetaData\Adapter\Apcu as MetaDataApcu;
use Phalcon\Mvc\Model\MetaData\Adapter\Libmemcached as MetaDataMemcached;
use Phalcon\Mvc\Model\MetaData\Adapter\Memory as MetaDataMemory;
use Phalcon\Mvc\Model\MetaData\Adapter\Redis as MetaDataRedis;
use Phalcon\Mvc\Model\MetaData\Adapter\Stream as MetaDataStream;
use Phalcon\Mvc\Url;
use Phalcon\Mvc\View;
use Phalcon\Mvc\View\Simple;
use Phalcon\Session\Adapter\Libmemcached as SessionLibmemcached;
use Phalcon\Session\Adapter\Noop as SessionNoop;
use Phalcon\Session\Adapter\Redis as SessionRedis;
use Phalcon\Session\Adapter\Stream as SessionStream;
use Phalcon\Session\Manager;
use Phalcon\Storage\AdapterFactory as StorageAdapterFactory;
use Phalcon\Storage\Exception;
use Phalcon\Storage\SerializerFactory;

use function getOptionsLibmemcached;
use function getOptionsModelCacheStream;
use function getOptionsMysql;
use function getOptionsPostgresql;
use function getOptionsRedis;
use function getOptionsSessionStream;
use function getOptionsSqlite;
use function outputDir;

/**
 * Trait DiTrait
 *
 * @property null|DiInterface $container
 */
trait DiTrait
{
    /**
     * @var null|DiInterface
     */
    protected $container = null;

    /**
     * @return DiInterface|null
     */
    protected function getDi()
    {
        return $this->container;
    }

    /**
     * Return a service from the container
     *
     * @param string $name
     *
     * @return mixed
     */
    protected function getService(string $name)
    {
        return $this->container->get($name);
    }

    /**
     * @param string $driver
     *
     * @return AdapterInterface
     */
    protected function newDbConnection(string $driver): AdapterInterface
    {
        switch ($driver) {
            case 'mysql':
                $options = getOptionsMysql();
                break;
            case 'pgsql':
                $options = getOptionsPostgresql();
                $driver  = 'postgresql';
                break;
            case 'sqlite':
                $options = getOptionsSqlite();
                break;
            case 'sqlsrv':
            default:
                $options = [];
        }

        $options['options'][PDO::ATTR_TIMEOUT] = 0;

        if ($driver !== 'sqlite') {
            $options['options'][PDO::ATTR_PERSISTENT] = 1;
        }

        return (new PdoFactory())->newInstance($driver, $options);
    }

    /**
     * @return AdapterInterface
     */
    protected function newDbService(): AdapterInterface
    {
        /** @var PDO $connection */
        $connection = $this->getConnection();
        $driver     = $connection->getAttribute(PDO::ATTR_DRIVER_NAME);

        return $this->newDbConnection($driver);
    }

    /**
     * Set up a new DI
     */
    protected function newDi()
    {
        Di::reset();
        $this->container = new Di();
        Di::setDefault($this->container);
    }

    /**
     * @param string $service
     * @param mixed|null $options
     *
     * @throws Exception
     * @return mixed|null
     */
    protected function newService(string $service, $options = null)
    {
        switch ($service) {
            case 'annotations':
                return new AnnotationsMemory(new SerializerFactory());
            case 'cliFactoryDefault':
                return new CliFactoryDefault();
            case 'console':
                return new Console($options);
            case 'crypt':
                return new Crypt();
            case 'eventsManager':
                return new EventsManager();
            case 'escaper':
                return new Escaper();
            case 'factoryDefault':
                return new FactoryDefault();
            case 'filter':
                return (new Filter\FilterFactory())->newInstance();
            case 'metadataMemory':
                return new MetaDataMemory();
            case 'metadataApcu':
                return new MetaDataApcu(
                    new AdapterFactory(new SerializerFactory()),
                    []
                );
            case 'metadataLibmemcached':
                return new MetaDataMemcached(
                    new AdapterFactory(new SerializerFactory()),
                    getOptionsLibmemcached()
                );
            case 'metadataRedis':
                return new MetaDataRedis(
                    new AdapterFactory(new SerializerFactory()),
                    getOptionsRedis()
                );
            case 'metadataStream':
                return new MetaDataStream(
                    ['metaDataDir' => outputDir()],
                );
            case 'modelsCacheLibmemcached':
                return new StorageLibmemcached(
                    new SerializerFactory(),
                    getOptionsLibmemcached()
                );
            case 'modelsCacheStream':
                return new StorageStream(
                    new SerializerFactory(),
                    getOptionsModelCacheStream()
                );
            case 'modelsManager':
                return new ModelsManager();
            case 'phpSerializer':
                return (new SerializerFactory())->newInstance('php');
            case 'profiler':
                return new Profiler();
            case 'request':
                return new Request();
            case 'response':
                return new Response();
            case 'sessionStream':
                return new SessionStream(getOptionsSessionStream());
            case 'sessionLibmemcached':
                return new SessionLibmemcached(
                    new StorageAdapterFactory(
                        new SerializerFactory()
                    ),
                    getOptionsLibmemcached()
                );
            case 'sessionNoop':
                return new SessionNoop();
            case 'sessionRedis':
                return new SessionRedis(
                    new StorageAdapterFactory(
                        new SerializerFactory()
                    ),
                    getOptionsRedis()
                );
            case 'url':
                return new Url();
            case 'view':
                return new View();
            case 'viewSimple':
                return new Simple();
            default:
                return null;
        }
    }

    /**
     * Reset the DI
     */
    protected function resetDi()
    {
        Di::reset();
    }

    protected function setDatabase(): void
    {
        $db = $this->newDbService();

        $this->container->setShared('db', $db);
    }

    /**
     * @param string $service
     *
     * @throws Exception
     */
    protected function setDiService(string $service)
    {
        $class = $this->newService($service);
        switch ($service) {
            case 'annotations':
            case 'console':
            case 'escaper':
            case 'eventsManager':
            case 'filter':
            case 'modelsManager':
            case 'modelsMetadata':
            case 'request':
            case 'response':
                $this->container->set($service, $class);
                break;
            case 'crypt':
                $this->container->set(
                    'crypt',
                    function () use ($class) {
                        $class->setKey('cryptkeycryptkey');

                        return $class;
                    }
                );
                break;

            case 'modelsCacheLibmemcached':
            case 'modelsCacheStream':
                $this->container->set('modelsCache', $class);
                break;

            case 'phpSerializer':
                $this->container->set('serializer', $class);
                break;

            case 'sessionStream':
            case 'sessionLibmemcached':
            case 'sessionNoop':
            case 'sessionRedis':
                $container = $this->container;
                $container->set(
                    'session',
                    function () use ($class, $container) {
                        $manager = new Manager();
                        $manager->setDI($container);
                        $manager->setAdapter($class);

                        return $manager;
                    }
                );
                break;

            case 'tag':
                $this->container->set(
                    $service,
                    function () {
                        $escaper = $this->container->get("escaper");

                        return new TagFactory($escaper);
                    }
                );
                break;

            case 'url':
                $this->container->set(
                    $service,
                    function () use ($class) {
                        $class->setBaseUri('/');

                        return $class;
                    }
                );
                break;
            case 'view':
            case 'viewSimple':
                $this->container->set(
                    $service,
                    function () use ($class) {
                        $class->setViewsDir(dataDir('fixtures/views/'));

                        return $class;
                    }
                );
                break;

            default:
                break;
        }
    }

    /**
     * Set up a new Cli\FactoryDefault
     *
     * @throws Exception
     */
    protected function setNewCliFactoryDefault()
    {
        CliFactoryDefault::reset();
        $this->container = $this->newService('cliFactoryDefault');
        CliFactoryDefault::setDefault($this->container);
    }

    /**
     * Set up a new FactoryDefault
     *
     * @throws Exception
     */
    protected function setNewFactoryDefault()
    {
        FactoryDefault::reset();
        $this->container = $this->newService('factoryDefault');
        FactoryDefault::setDefault($this->container);
    }
}
