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
use Phalcon\Mvc\Model\Exceptions\IndexNotInRow;
use Phalcon\Mvc\Model\Exceptions\RowIsImmutable;
use Phalcon\Mvc\ModelInterface;
use stdClass;

/**
 * This component allows Phalcon\Mvc\Model to return rows without an associated entity.
 * This objects implements the ArrayAccess interface to allow access the object as object->x or array[x].
 *
 * @phpstan-implements ArrayAccess<array-key, mixed>
 */
class Row extends stdClass implements EntityInterface, ResultInterface, ArrayAccess, JsonSerializable
{
    /**
     * Serializes the object for json_encode
     *
     * @phpstan-return array<array-key, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Checks whether offset exists in the row
     *
     * @phpstan-param array-key $index
     */
    public function offsetExists(mixed $index): bool
    {
        return property_exists($this, (string) $index);
    }

    /**
     * Gets a record in a specific position of the row
     *
     * @throws Exception
     *
     * @phpstan-param array-key $index
     */
    public function offsetGet(mixed $index): mixed
    {
        $key = (string) $index;

        if (!property_exists($this, $key)) {
            throw new IndexNotInRow();
        }

        return $this->$key;
    }

    /**
     * Rows cannot be changed. It has only been implemented to meet the
     * definition of the ArrayAccess interface
     *
     * @throws Exception
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new RowIsImmutable();
    }

    /**
     * Rows cannot be changed. It has only been implemented to meet the
     * definition of the ArrayAccess interface
     *
     * @throws Exception
     */
    public function offsetUnset(mixed $offset): void
    {
        throw new RowIsImmutable();
    }

    /**
     * Reads an attribute value by its name
     *
     *```php
     * echo $invoice->readAttribute("inv_title");
     *```
     *
     * @return mixed
     */
    public function readAttribute(string $attribute)
    {
        return $this->$attribute ?? null;
    }

    /**
     * Set the current object's state
     */
    public function setDirtyState(int $dirtyState): bool | ModelInterface
    {
        return false;
    }

    /**
     * Returns the instance as an array representation
     *
     * @phpstan-return array<array-key, mixed>
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }

    /**
     * Writes an attribute value by its name
     *
     *```php
     * $invoice->writeAttribute("inv_title", "Test Invoice");
     *```
     */
    public function writeAttribute(string $attribute, mixed $value): void
    {
        $this->$attribute = $value;
    }
}
