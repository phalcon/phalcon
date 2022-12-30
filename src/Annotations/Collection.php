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

namespace Phalcon\Annotations;

use Countable;
use Iterator;

/**
 * Represents a collection of annotations. This class allows to traverse a group
 * of annotations easily
 *
 *```php
 * // Traverse annotations
 * foreach ($classAnnotations as $annotation) {
 *     echo "Name=", $annotation->getName(), PHP_EOL;
 * }
 *
 * // Check if the annotations has a specific
 * var_dump($classAnnotations->has("Cacheable"));
 *
 * // Get an specific annotation in the collection
 * $annotation = $classAnnotations->get("Cacheable");
 *```
 */
class Collection implements Iterator, Countable
{
    /**
     * @var array
     */
    protected array $annotations;

    /**
     * @var int
     */
    protected int $position = 0;

    /**
     * Constructor
     */
    public function __construct(array $reflectionData = [])
    {
        $annotations = [];
        foreach ($reflectionData as $annotationData) {
            $annotations[] = new Annotation($annotationData);
        }

        $this->annotations = $annotations;
    }

    /**
     * Returns the number of annotations in the collection
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->annotations);
    }

    /**
     * Returns the current annotation in the iterator
     *
     * @return mixed
     */
    public function current(): mixed
    {
        return $this->annotations[$this->position] ?? false;
    }

    /**
     * Returns the first annotation that match a name
     *
     * @param string $name
     *
     * @return Annotation
     * @throws Exception
     */
    public function get(string $name): Annotation
    {
        foreach ($this->annotations as $annotation) {
            if ($name == $annotation->getName()) {
                return $annotation;
            }
        }

        throw new Exception(
            "Collection does not have an annotation called '" . $name . "'"
        );
    }

    /**
     * Returns all the annotations that match a name
     *
     * @param string $name
     *
     * @return Annotation[]
     */
    public function getAll(string $name): array
    {
        $found = [];
        foreach ($this->annotations as $annotation) {
            if ($name == $annotation->getName()) {
                $found[] = $annotation;
            }
        }

        return $found;
    }

    /**
     * Returns the internal annotations as an array
     *
     * @return Annotation[]
     */
    public function getAnnotations(): array
    {
        return $this->annotations;
    }

    /**
     * Check if an annotation exists in a collection
     *
     * @param string $name
     *
     * @return bool
     */
    public function has(string $name): bool
    {
        foreach ($this->annotations as $annotation) {
            if ($name == $annotation->getName()) {
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
     * Check if the current annotation in the iterator is valid
     *
     * @return bool
     */
    public function valid(): bool
    {
        return isset($this->annotations[$this->position]);
    }
}
