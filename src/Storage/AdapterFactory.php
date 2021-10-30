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

namespace Phalcon\Storage;

use Phalcon\Storage\Adapter\AdapterInterface;
use Phalcon\Storage\Adapter\Apcu;
use Phalcon\Storage\Adapter\Libmemcached;
use Phalcon\Storage\Adapter\Memory;
use Phalcon\Storage\Adapter\Redis;
use Phalcon\Storage\Adapter\Stream;
use Phalcon\Traits\Factory\FactoryTrait;

/**
 * Class AdapterFactory
 *
 * @property SerializerFactory $serializerFactory
 */
class AdapterFactory
{
    use FactoryTrait;

    /**
     * @var SerializerFactory
     */
    private SerializerFactory $serializerFactory;

    /**
     * AdapterFactory constructor.
     *
     * @param SerializerFactory $factory
     * @param array             $services
     */
    public function __construct(
        SerializerFactory $factory,
        array $services = []
    ) {
        $this->serializerFactory = $factory;

        $this->init($services);
    }

    /**
     * Create a new instance of the adapter
     *
     * @param string $name
     * @param array  $options
     *
     * @return AdapterInterface
     * @throws Exception
     */
    public function newInstance(string $name, array $options = []): AdapterInterface
    {
        $definition = $this->getService($name);

        return new $definition($this->serializerFactory, $options);
    }

    /**
     * @return string
     */
    protected function getExceptionClass(): string
    {
        return Exception::class;
    }

    /**
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
