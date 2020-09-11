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

use Phalcon\Factory\AbstractFactory;
use Phalcon\Factory\Exception;
use Phalcon\Factory\FactoryTrait;
use Phalcon\Storage\Serializer\SerializerInterface;

/**
 * Class SerializerFactory
 *
 * @package Phalcon\Storage
 */
class SerializerFactory
{
    use FactoryTrait;

    /**
     * SerializerFactory constructor.
     *
     * @param array $services
     */
    public function __construct(array $services = [])
    {
        $this->init($services);
    }

    /**
     * @param string $name
     *
     * @return SerializerInterface
     * @throws Exception
     */
    public function newInstance(string $name): SerializerInterface
    {
        $definition = $this->getService($name);

        return new $definition();
    }

    /**
     * @return array
     */
    protected function getAdapters(): array
    {
        return [
            'base64'   => 'Phalcon\Storage\Serializer\Base64',
            'igbinary' => 'Phalcon\Storage\Serializer\Igbinary',
            'json'     => 'Phalcon\Storage\Serializer\Json',
            'msgpack'  => 'Phalcon\Storage\Serializer\Msgpack',
            'none'     => 'Phalcon\Storage\Serializer\None',
            'php'      => 'Phalcon\Storage\Serializer\Php',
        ];
    }
}
