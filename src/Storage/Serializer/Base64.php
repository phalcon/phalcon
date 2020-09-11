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

use InvalidArgumentException;

use function base64_decode;
use function base64_encode;
use function is_string;

/**
 * Class Base64
 *
 * @package Phalcon\Storage\Serializer
 */
class Base64 extends AbstractSerializer
{
    /**
     * Serializes data
     *
     * @return string
     */
    public function serialize(): string
    {
        if (!is_string($this->data)) {
            throw new InvalidArgumentException(
                'Data for the serializer must of type string'
            );
        }

        return base64_encode($this->data);
    }

    /**
     * Unserializes data
     *
     * @param string $data
     */
    public function unserialize($data): void
    {
        if (!is_string($data)) {
            throw new InvalidArgumentException(
                'Data for the unserializer must of type string'
            );
        }

        $this->data = base64_decode($data);
    }
}
