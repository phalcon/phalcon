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

namespace Phalcon\Annotations\Parser;

use ArrayIterator;
use IteratorAggregate;
use Traversable;

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
 *
 * @template TKey of int
 * @template TValue of Annotation
 *```
 */
class Collection implements IteratorAggregate
{
    protected array $annotations;

    protected int $position = 0;

    /**
     * Constructor
     *
     * @param array $reflectionData
     */
    public function __construct(array $reflectionData = [])
    {
        $this->annotations = [];
        foreach ($reflectionData as $annotationData) {
            $this->annotations[] = new Annotation($annotationData);
        }
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
            if ($name === $annotation->getName()) {
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
     * @return TValue[]
     */
    public function getAll(string $name): array
    {
        $found = [];
        foreach ($this->annotations as $annotation) {
            if ($name === $annotation->getName()) {
                $found[] = $annotation;
            }
        }

        return $found;
    }

    public function getAnnotations(): Traversable
    {
        return new ArrayIterator($this->annotations);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->annotations);
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
            if ($name === $annotation->getName()) {
                return true;
            }
        }

        return false;
    }
}
