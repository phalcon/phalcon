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

namespace Phalcon\Queue;

use Phalcon\Contracts\Queue\ConnectionFactory as ConnectionFactoryInterface;
use Phalcon\Queue\Adapter\Memory\MemoryConnectionFactory;
use Phalcon\Queue\Adapter\Stream\StreamConnectionFactory;
use Phalcon\Queue\Exceptions\Exception;
use Phalcon\Traits\Factory\FactoryTrait;

/**
 * Maps an adapter name to its ConnectionFactory. Mirrors
 * Phalcon\Storage\AdapterFactory.
 */
class AdapterFactory
{
    use FactoryTrait;

    public function __construct(array $services = [])
    {
        $this->init($services);
    }

    public function newInstance(string $name, array $options = []): ConnectionFactoryInterface
    {
        $definition = $this->getService($name);

        return new $definition($options);
    }

    protected function getExceptionClass(): string
    {
        return Exception::class;
    }

    /**
     * Returns the available adapters. Stream, Redis and Beanstalk are added
     * in their respective phases.
     *
     * @return string[]
     */
    protected function getServices(): array
    {
        return [
            'memory' => MemoryConnectionFactory::class,
            'stream' => StreamConnectionFactory::class,
        ];
    }
}
