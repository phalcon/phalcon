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
use Phalcon\Factory\AbstractConfigFactory;

/**
 * Creates a new Cache class
 */
class CacheFactory extends AbstractConfigFactory
{
    /**
     * Constructor
     */
    public function __construct(
        protected AdapterFactory $adapterFactory
    ) {
    }

    /**
     * Factory to create an instance from a Config object
     *
     * @param array|ConfigInterface $config = [
     *     'adapter' => 'apcu',
     *     'options' => [
     *         'servers' => [
     *             [
     *                 'host' => 'localhost',
     *                 'port' => 11211,
     *                 'weight' => 1,
     *             ]
     *         ],
     *         'host' => '127.0.0.1',
     *         'port' => 6379,
     *         'index' => 0,
     *         'persistent' => false,
     *         'auth' => '',
     *         'socket' => '',
     *         'defaultSerializer' => 'Php',
     *         'lifetime' => 3600,
     *         'serializer' => null,
     *         'prefix' => 'phalcon',
     *         'storageDir' => ''
     *     ]
     * ]
     *
     * @return CacheInterface
     * @throws Exception
     */
    public function load(mixed $config): CacheInterface
    {
        $config = $this->checkConfig($config);
        $this->checkConfigElement($config, 'adapter');

        /** @var string $name */
        $name    = $config['adapter'];
        $options = $config['options'] ?? [];

        return $this->newInstance($name, $options);
    }

    /**
     * Constructs a new Cache instance.
     *
     * @param array  $options = [
     *      'servers'           => [
     *          [
     *              'host' => 'localhost',
     *              'port' => 11211,
     *              'weight' => 1,
     *          ]
     *      ],
     *      'host'              => '127.0.0.1',
     *      'port'              => 6379,
     *      'index'             => 0,
     *      'persistent'        => false,
     *      'auth'              => '',
     *      'socket'            => '',
     *      'defaultSerializer' => 'Php',
     *      'lifetime'          => 3600,
     *      'serializer'        => null,
     *      'prefix'            => 'phalcon',
     *      'storageDir'        => '',
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

    /**
     * Returns the exception class for the factory
     *
     * @return string
     */
    protected function getExceptionClass(): string
    {
        return Exception::class;
    }
}
