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

use function igbinary_serialize;

use const E_WARNING;

/**
 * Class Igbinary
 *
 * @package Phalcon\Storage\Serializer
 */
class Igbinary extends AbstractSerializer
{
    use StorageErrorHandlerTrait;

    /**
     * Serializes data
     *
     * @return string
     */
    public function serialize()
    {
        if (true !== $this->isSerializable($this->data)) {
            return $this->data;
        }

        return igbinary_serialize($this->data);
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
            'igbinary_unserialize',
            E_WARNING,
            $data
        );
    }
}
