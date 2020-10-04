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
use function restore_error_handler;
use function set_error_handler;

use const E_WARNING;

/**
 * Class Msgpack
 *
 * @package Phalcon\Storage\Serializer
 */
class Msgpack extends AbstractSerializer
{
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
        $warning = false;
        set_error_handler(
            function () use (&$warning) {
                $warning = true;
            },
            E_WARNING
        );

        $data = msgpack_unpack($data);

        restore_error_handler();

        if (true === $warning) {
            $data = null;
        }

        $this->data = $data;
    }
}
