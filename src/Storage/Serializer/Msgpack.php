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

class Msgpack extends Igbinary
{
    /**
     * Serializes data
     *
     * @return string|null
     */
    public function serialize(): mixed
    {
        if (true !== $this->isSerializable($this->data)) {
            return $this->data;
        }

        return msgpack_pack($this->data);
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
