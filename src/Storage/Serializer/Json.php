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
use JsonSerializable;

use function is_object;
use function json_decode;
use function json_encode;

/**
 * Class Json
 *
 * @package Phalcon\Storage\Serializer
 */
class Json extends AbstractSerializer
{
    /**
     * Serializes data
     *
     * @return JsonSerializable|mixed|string
     */
    public function serialize()
    {
        if (true === is_object($this->data) && !($this->data instanceof JsonSerializable)) {
            throw new InvalidArgumentException(
                'Data for the JSON serializer cannot be of type "object" ' .
                'without implementing "JsonSerializable"'
            );
        }

        if (true !== $this->isSerializable($this->data)) {
            return $this->data;
        }

        return json_encode($this->data);
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
        $this->data = json_decode($data);
    }
}
