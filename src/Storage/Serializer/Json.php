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
use JsonException;
use JsonSerializable;

use function is_object;
use function json_decode;
use function json_encode;

use const JSON_THROW_ON_ERROR;

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
     * @return false|JsonSerializable|mixed|string|null
     * @throws JsonException
     */
    public function serialize()
    {
        if (is_object($this->data) && !($this->data instanceof JsonSerializable)) {
            throw new InvalidArgumentException(
                'Data for the JSON serializer cannot be of type "object" ' .
                'without implementing "JsonSerializable"'
            );
        }

        return parent::serialize();
    }

    /**
     * Unserializes data
     *
     * @param string $data
     *
     * @throws JsonException
     */
    public function unserialize($data): void
    {
        $this->data = $this->internalUnserlialize($data);
    }

    /**
     * @param mixed $data
     *
     * @return false|string
     * @throws JsonException
     */
    protected function internalSerialize($data)
    {
        return json_encode($this->data, 79 + JSON_THROW_ON_ERROR);
    }

    /**
     * @param mixed $data
     *
     * @return mixed
     * @throws JsonException
     */
    protected function internalUnserlialize($data)
    {
        return json_decode(
            $data,
            false,
            512,
            JSON_THROW_ON_ERROR
        );
    }
}
