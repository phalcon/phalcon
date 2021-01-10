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

use Phiz\Storage\Serializer\Base64;
use Phiz\Storage\Serializer\Igbinary;
use Phiz\Storage\Serializer\Json;
use Phiz\Storage\Serializer\Msgpack;
use Phiz\Storage\Serializer\None;
use Phiz\Storage\Serializer\Php;
use Phiz\Storage\Serializer\SerializerInterface;
use Phiz\Support\Exception as SupportException;
use Phiz\Support\Traits\FactoryTrait;

/**
 * Class SerializerFactory
 *
 * @package Phiz\Storage
 */
class SerializerFactory
{
    use FactoryTrait;

    /**
     * @param string $name
     *
     * @return SerializerInterface
     * @throws SupportException
     */
    public function newInstance(string $name): SerializerInterface
    {
        $definition = $this->getService($name);

        return new $definition();
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
