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

use Phalcon\Storage\Traits\StorageErrorHandlerTrait;

use function msgpack_pack;

use const E_WARNING;

/**
 * Class Msgpack
 *
 * @package Phalcon\Storage\Serializer
 */
class Msgpack extends AbstractSerializer
{
    use StorageErrorHandlerTrait;

    /**
     * Serializes data
     *
     * @return string|null
     */
    public function serialize()
    {
        if (true !== $this->isSerializable($this->data)) {
            return $this->data;
        }

        return msgpack_pack($this->data);
    }

    /**
     * Unserializes data
     *
     * @param string $data
     *
     * @return void
     */
    public function unserialize($data)
    {
        $this->data = $this->callMethodWithError(
            'msgpack_unpack',
            E_WARNING,
            $data
        );
    }
}
