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

namespace Phalcon\Storage\Serializer;

/**
 * Class Msgpack
 *
 * @package Phalcon\Storage\Serializer
 */
class Msgpack extends AbstractSerializer
{
    /**
     * @param mixed $data
     *
     * @return mixed
     */
    protected function internalSerialize($data)
    {
        return msgpack_pack($data);
    }

    /**
     * @param mixed $data
     *
     * @return mixed
     */
    protected function internalUnserlialize($data)
    {
        return msgpack_unpack($data);
    }
}
