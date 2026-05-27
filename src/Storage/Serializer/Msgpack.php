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

use function msgpack_pack;
use function msgpack_unpack;

class Msgpack extends Igbinary
{
    /**
     * Serializes data
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function doSerialize(mixed $value): string
    {
        return msgpack_pack($value);
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    protected function doUnserialize($value)
    {
        return msgpack_unpack($value);
    }
}
