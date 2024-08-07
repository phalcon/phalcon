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

namespace Phalcon\Mvc\Model;

use ArrayAccess;
use JsonSerializable;
use Phalcon\Mvc\EntityInterface;
use Phalcon\Mvc\ModelInterface;
use stdClass;

/**
 * This component allows Phalcon\Mvc\Model to return rows without an associated entity.
 * This objects implements the ArrayAccess interface to allow access the object as object->x or array[x].
 */
class Row extends stdClass implements EntityInterface, ResultInterface, ArrayAccess, JsonSerializable
{
    /**
     * Serializes the object for json_encode
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Checks whether offset exists in the row
     *
     * @param mixed $offset
     *
     * @return bool
     */
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->$offset);
    }

    /**
     * Gets a record in a specific position of the row
     *
     * @param mixed $offset
     *
     * @return mixed
     * @throws Exception
     */
    public function offsetGet(mixed $offset): mixed
    {
        if (!$this->offsetExists($offset)) {
            throw new Exception("The index does not exist in the row");
        }

        return $this->$offset;
    }

    /**
     * Rows cannot be changed. It has only been implemented to meet the
     * definition of the ArrayAccess interface
     *
     * @param mixed $offset
     * @param mixed $value
     *
     * @return void
     * @throws Exception
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new Exception("Row is an immutable ArrayAccess object");
    }

    /**
     * Rows cannot be changed. It has only been implemented to meet the
     * definition of the ArrayAccess interface
     *
     * @param mixed $offset
     *
     * @return void
     * @throws Exception
     */
    public function offsetUnset(mixed $offset): void
    {
        throw new Exception("Row is an immutable ArrayAccess object");
    }

    /**
     * Reads an attribute value by its name
     *
     *```php
     * echo $robot->readAttribute("name");
     *```
     *
     * @param string $attribute
     *
     * @return mixed
     */
    public function readAttribute(string $attribute): mixed
    {
        return $this->$attribute ?? null;
    }

    /**
     * Set the current object's state
     *
     * @param int $dirtyState
     *
     * @return ModelInterface|bool
     */
    public function setDirtyState(int $dirtyState): ModelInterface | bool
    {
        return false;
    }

    /**
     * Returns the instance as an array representation
     *
     * @return array
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }

    /**
     * Writes an attribute value by its name
     *
     *```php
     * $robot->writeAttribute("name", "Rosey");
     *```
     *
     * @param string $attribute
     * @param mixed  $value
     *
     * @return void
     */
    public function writeAttribute(string $attribute, mixed $value): void
    {
        $this->$attribute = $value;
    }
}
