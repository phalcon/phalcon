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

use Phalcon\Storage\Serializer\Base64;
use Phalcon\Storage\Serializer\Igbinary;
use Phalcon\Storage\Serializer\Json;
use Phalcon\Storage\Serializer\Msgpack;
use Phalcon\Storage\Serializer\None;
use Phalcon\Storage\Serializer\Php;
use Phalcon\Storage\Serializer\SerializerInterface;
use Phalcon\Traits\Factory\FactoryTrait;

/**
 * Class SerializerFactory
 *
 * @package Phalcon\Storage
 */
class SerializerFactory
{
    use FactoryTrait;

    /**
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
            'base64'   => Base64::class,
            'igbinary' => Igbinary::class,
            'json'     => Json::class,
            'msgpack'  => Msgpack::class,
            'none'     => None::class,
            'php'      => Php::class,
        ];
    }
}
