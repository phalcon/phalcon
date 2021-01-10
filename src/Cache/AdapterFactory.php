<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phiz\Cache;

use Phiz\Cache\Adapter\AdapterInterface;
use Phiz\Cache\Adapter\Apcu;
use Phiz\Cache\Adapter\Libmemcached;
use Phiz\Cache\Adapter\Memory;
use Phiz\Cache\Adapter\Redis;
use Phiz\Cache\Adapter\Stream;
use Phiz\Storage\SerializerFactory;
use Phiz\Support\Exception as SupportException;
use Phiz\Support\HelperFactory;
use Phiz\Support\Traits\FactoryTrait;

/**
 * Factory to create Cache adapters
 *
 * @property HelperFactory     $helperFactory
 * @property SerializerFactory $serializerFactory
 */
class AdapterFactory
{
    use FactoryTrait;

    /**
     * @var HelperFactory
     */
    private HelperFactory $helperFactory;

    /**
     * @var SerializerFactory
     */
    private ?SerializerFactory $serializerFactory;

    /**
     * AdapterFactory constructor.
     *
     * @param HelperFactory     $helperFactory
     * @param SerializerFactory $factory
     * @param array             $services
     */
    public function __construct(
        HelperFactory $helperFactory,
        SerializerFactory $factory,
        array $services = []
    ) {
        $this->helperFactory     = $helperFactory;
        $this->serializerFactory = $factory;

        $this->init($services);
    }

    /**
     * Create a new instance of the adapter
     *
     * @param string $name
     * @param array  $options = [
     *     'servers' => [
     *         [
     *             'host'   => 'localhost',
     *             'port'   => 11211,
     *             'weight' => 1,
     *         ]
     *     ],
     *     'host'              => '127.0.0.1',
     *     'port'              => 6379,
     *     'index'             => 0,
     *     'persistent'        => false,
     *     'auth'              => '',
     *     'socket'            => '',
     *     'defaultSerializer' => 'Php',
     *     'lifetime'          => 3600,
     *     'serializer'        => null,
     *     'prefix'            => 'phalcon',
     *     'storageDir'        => ''
     * ]
     *
     * @return AdapterInterface
     * @throws SupportException
     */
    public function newInstance(string $name, array $options = []): AdapterInterface
    {
        $definition = $this->getService($name);

        return new $definition(
            $this->helperFactory,
            $this->serializerFactory,
            $options
        );
    }

    /**
     * Returns the available adapters
     *
     * @return array
     */
    protected function getServices(): array
    {
        return [
            'apcu'         => Apcu::class,
            'libmemcached' => Libmemcached::class,
            'memory'       => Memory::class,
            'redis'        => Redis::class,
            'stream'       => Stream::class,
        ];
    }
}
