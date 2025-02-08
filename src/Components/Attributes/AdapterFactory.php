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

namespace Phalcon\Components\Attributes;

use Exception as BaseException;
use Phalcon\Components\Attributes\Adapter\AdapterInterface;
use Phalcon\Components\Attributes\Adapter\Apcu;
use Phalcon\Components\Attributes\Adapter\Libmemcached;
use Phalcon\Components\Attributes\Adapter\Memory;
use Phalcon\Components\Attributes\Adapter\Redis;
use Phalcon\Components\Attributes\Adapter\Stream;
use Phalcon\Components\Attributes\Adapter\Weak;
use Phalcon\Components\Attributes\Parser\Exception;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Traits\Factory\FactoryTrait;

/**
 * Factory to create Attributes adapters
 *
 * @property SerializerFactory $serializerFactory
 */
class AdapterFactory
{
    use FactoryTrait;

    /**
     * @var SerializerFactory|null
     */
    private ?SerializerFactory $serializerFactory;

    /**
     * AdapterFactory constructor.
     *
     * @param SerializerFactory     $factory
     * @param array<string, string> $services
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
     * @throws BaseException
     * @return AdapterInterface
     */
    public function newInstance(string $name, array $options = []): AdapterInterface
    {
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
     * @return array<string, string>
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
