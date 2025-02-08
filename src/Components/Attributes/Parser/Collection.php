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

namespace Phalcon\Components\Attributes\Parser;

use Countable;
use Iterator;

/**
 * Represents a collection of attributes. This class allows to traverse a group
 * of attributes easily
 *
 *```php
 * // Traverse attributes
 * foreach ($classAttributes as $attribute) {
 *     echo "Name=", $attribute->getName(), PHP_EOL;
 * }
 *
 * // Check if the attributes has a specific
 * var_dump($classAttributes->has("Cacheable"));
 *
 * // Get an specific attribute in the collection
 * $attribute = $classAttributes->get("Cacheable");
 *```
 */
class Collection implements Iterator, Countable
{
    /**
     * @var array
     */
    protected array $attributes;

    /**
     * @var int
     */
    protected int $position = 0;

    /**
     * Constructor
     *
     * @param array $reflectionData
     */
    public function __construct(array $reflectionData = [])
    {
        $attributes = [];
        foreach ($reflectionData as $attributeData) {
            $attributes[] = new Attribute($attributeData);
        }

        $this->attributes = $attributes;
    }

    /**
     * Returns the number of attributes in the collection
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->attributes);
    }

    /**
     * Returns the current attribute in the iterator
     *
     * @return mixed
     */
    public function current(): mixed
    {
        return $this->attributes[$this->position] ?? false;
    }

    /**
     * Returns the first attribute that match a name
     *
     * @param string $name
     *
     * @throws Exception
     * @return Attribute
     */
    public function get(string $name): Attribute
    {
        foreach ($this->attributes as $attribute) {
            if ($name == $attribute->getName()) {
                return $attribute;
            }
        }

        throw new Exception(
            "Collection does not have an attribute called '" . $name . "'"
        );
    }

    /**
     * Returns all the attributes that match a name
     *
     * @param string $name
     *
     * @return Attribute[]
     */
    public function getAll(string $name): array
    {
        $found = [];
        foreach ($this->attributes as $attribute) {
            if ($name == $attribute->getName()) {
                $found[] = $attribute;
            }
        }

        return $found;
    }

    /**
     * Returns the internal attributes as an array
     *
     * @return Attribute[]
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Check if an attribute exists in a collection
     *
     * @param string $name
     *
     * @return bool
     */
    public function has(string $name): bool
    {
        foreach ($this->attributes as $attribute) {
            if ($name == $attribute->getName()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the current position/key in the iterator
     *
     * @return int
     */
    public function key(): int
    {
        return $this->position;
    }

    /**
     * Moves the internal iteration pointer to the next position
     *
     * @return void
     */
    public function next(): void
    {
        $this->position++;
    }

    /**
     * Rewinds the internal iterator
     *
     * @return void
     */
    public function rewind(): void
    {
        $this->position = 0;
    }

    /**
     * Check if the current attribute in the iterator is valid
     *
     * @return bool
     */
    public function valid(): bool
    {
        return isset($this->attributes[$this->position]);
    }
}
