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

use Phalcon\Cache\Exception\Exception;
use Phalcon\Config\ConfigInterface;
use Phalcon\Support\Traits\ConfigTrait;
use Psr\SimpleCache\CacheInterface;

/**
 * Creates a new Cache class
 *
 * @property AdapterFactory $adapterFactory;
 */
class CacheFactory
{
    use ConfigTrait;

    /**
     * @var AdapterFactory
     */
    protected AdapterFactory $adapterFactory;

    /**
     * Constructor
     */
    public function __construct(AdapterFactory $factory)
    {
        $this->adapterFactory = $factory;
    }

    /**
     * Factory to create an instance from a Config object
     *
     * @param array<string, mixed>|ConfigInterface $config = [
     *                                                     'adapter' => 'apcu',
     *                                                     'options' => [
     *                                                     'servers' => [
     *                                                     [
     *                                                     'host' => 'localhost',
     *                                                     'port' => 11211,
     *                                                     'weight' => 1,
     *                                                     ]
     *                                                     ],
     *                                                     'host' => '127.0.0.1',
     *                                                     'port' => 6379,
     *                                                     'index' => 0,
     *                                                     'persistent' => false,
     *                                                     'auth' => '',
     *                                                     'socket' => '',
     *                                                     'defaultSerializer' => 'Php',
     *                                                     'lifetime' => 3600,
     *                                                     'serializer' => null,
     *                                                     'prefix' => 'phalcon',
     *                                                     'storageDir' => ''
     *                                                     ]
     *                                                     ]
     *
     * @return CacheInterface
     * @throws Exception
     */
    public function load($config): CacheInterface
    {
        $config  = $this->checkConfig($config);
        $name    = $config['adapter'];
        $options = $config['options'] ?? [];

        return $this->newInstance($name, $options);
    }

    /**
     * Constructs a new Cache instance.
     *
     * @param string               $name
     * @param array<string, mixed> $options = [
     *     'servers'           => [
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
     *     'storageDir'        => '',
     * ]
     *
     * @return CacheInterface
     * @throws Exception
     */
    public function newInstance(string $name, array $options = []): CacheInterface
    {
        $adapter = $this->adapterFactory->newInstance($name, $options);

        return new Cache($adapter);
    }
}
