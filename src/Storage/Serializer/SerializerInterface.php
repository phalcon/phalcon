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

interface SerializerInterface //extends Serializable
{
    /**
     * @return mixed
     */
    public function getData(): mixed;

    /**
     * Serializes data
     *
     * @return mixed
     */
    public function serialize(): mixed;

    /**
     * @param mixed $data
     */
    public function setData($data): void;

    /**
     * Unserializes data
     *
     * @param mixed $data
     */
    public function unserialize(mixed $data): void;
}
