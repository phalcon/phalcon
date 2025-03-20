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

namespace Phalcon\Support\Collection\Traits;

use function serialize;
use function unserialize;

trait SerializableTrait
{
    abstract public function init(array $data = []): void;

    /**
     * String representation of object
     *
     * @link https://php.net/manual/en/serializable.serialize.php
     */
    public function serialize(): string
    {
        return serialize($this->toArray());
    }

    /**
     * Returns the object in an array format
     *
     * @return array
     */
    abstract public function toArray(): array;

    /**
     * Constructs the object
     *
     * @link https://php.net/manual/en/serializable.unserialize.php
     *
     * @param string $serialized
     *
     * @return void
     */
    public function unserialize($serialized): void
    {
        $serialized = (string)$serialized;
        $data       = unserialize($serialized);

        $this->init($data);
    }
}
