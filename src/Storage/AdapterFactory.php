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

namespace Phiz\Storage;

use Phiz\Storage\Adapter\AdapterInterface;
use Phiz\Storage\Adapter\Apcu;
use Phiz\Storage\Adapter\Libmemcached;
use Phiz\Storage\Adapter\Memory;
use Phiz\Storage\Adapter\Redis;
use Phiz\Storage\Adapter\Stream;
use Phiz\Support\Exception as SupportException;
use Phiz\Support\HelperFactory;
use Phiz\Support\Traits\FactoryTrait;

/**
 * Class AdapterFactory
 *
 * @property HelperFactory     $helperFactory;
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
    private SerializerFactory $serializerFactory;

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
     * @param array  $options
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
