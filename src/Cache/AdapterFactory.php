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

namespace Phalcon\Cache;

use Exception as BaseException;
use Phalcon\Cache\Adapter\AdapterInterface;
use Phalcon\Cache\Adapter\Apcu;
use Phalcon\Cache\Adapter\Libmemcached;
use Phalcon\Cache\Adapter\Memory;
use Phalcon\Cache\Adapter\Redis;
use Phalcon\Cache\Adapter\Stream;
use Phalcon\Cache\Adapter\Weak;
use Phalcon\Cache\Exception\Exception;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Traits\Factory\FactoryTrait;

/**
 * Factory to create Cache adapters
 *
 */
class AdapterFactory
{
    use FactoryTrait;

    /**
     * AdapterFactory constructor.
     *
     * @param SerializerFactory     $serializerFactory
     * @param array<string, string> $services
     */
    public function __construct(
        protected readonly SerializerFactory $serializerFactory,
        array $services = []
    ) {
        $this->init($services);
    }

    /**
     * Create a new instance of the adapter
     *
     * @param string               $name
     * @param array<string, mixed> $options = [
     *                                      'servers' => [
     *                                      [
     *                                      'host'   => 'localhost',
     *                                      'port'   => 11211,
     *                                      'weight' => 1,
     *                                      ]
     *                                      ],
     *                                      'host'              => '127.0.0.1',
     *                                      'port'              => 6379,
     *                                      'index'             => 0,
     *                                      'persistent'        => false,
     *                                      'auth'              => '',
     *                                      'socket'            => '',
     *                                      'defaultSerializer' => 'Php',
     *                                      'lifetime'          => 3600,
     *                                      'serializer'        => null,
     *                                      'prefix'            => 'phalcon',
     *                                      'storageDir'        => ''
     *                                      ]
     *
     * @return AdapterInterface
     * @throws BaseException
     */
    public function newInstance(string $name, array $options = []): AdapterInterface
    {
        /** @var class-string $definition */
        $definition = $this->getService($name);

        return new $definition(
            $this->serializerFactory,
            $options
        );
    }

    /**
     * @return string
     */
    protected function getExceptionClass(): string
    {
        return Exception::class;
    }

    /**
     * Returns the available adapters
     *
     * @return string[]
     */
    protected function getServices(): array
    {
        return [
            "apcu"         => Apcu::class,
            "libmemcached" => Libmemcached::class,
            "memory"       => Memory::class,
            "redis"        => Redis::class,
            "stream"       => Stream::class,
            "weak"         => Weak::class,
        ];
    }
}
