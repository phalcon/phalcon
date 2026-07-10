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

use Phalcon\Storage\Serializer\Exceptions\InvalidSerializationInput;
use Phalcon\Storage\Serializer\Exceptions\InvalidUnserializationInput;
use Phalcon\Traits\Php\Base64Trait;

use function is_string;

class Base64 extends AbstractSerializer
{
    use Base64Trait;

    /**
     * Serializes data
     *
     * @return string
     */
    public function serialize(): mixed
    {
        if (!is_string($this->data)) {
            throw new InvalidSerializationInput();
        }

        return $this->phpBase64Encode($this->data);
    }

    /**
     * Unserializes data
     *
     * @param string $data
     *
     * @retrun void
     */
    public function unserialize(mixed $data): void
    {
        if (!is_string($data)) {
            throw new InvalidUnserializationInput();
        }

        $result = $this->phpBase64Decode($data, true);

        if (false === $result) {
            $this->isSuccess = false;
            $result          = "";
        } else {
            $this->isSuccess = true;
        }

        $this->data = $result;
    }
}
