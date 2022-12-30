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

use function is_array;

/**
 * Allows to manipulate the annotations reflection in an OO manner
 *
 *```php
 * use Phalcon\Annotations\Reader;
 * use Phalcon\Annotations\Reflection;
 *
 * // Parse the annotations in a class
 * $reader = new Reader();
 * $parsing = $reader->parse("MyComponent");
 *
 * // Create the reflection
 * $reflection = new Reflection($parsing);
 *
 * // Get the annotations in the class docblock
 * $classAnnotations = $reflection->getClassAnnotations();
 *```
 */
class Reflection
{
    /**
     * @var Collection|null
     */
    protected ?Collection $classAnnotations = null;

    /**
     * @var array
     */
    protected array $constantAnnotations = [];

    /**
     * @var array
     */
    protected array $propertyAnnotations = [];

    /**
     * @var array
     */
    protected array $methodAnnotations = [];

    /**
     * Constructor
     *
     * @param array $reflectionData
     */
    public function __construct(
        protected array $reflectionData = []
    ) {
    }

    /**
     * Returns the annotations found in the class docblock
     *
     * @return Collection|null
     */
    public function getClassAnnotations(): Collection|null
    {
        if (
            null === $this->classAnnotations &&
            isset($this->reflectionData["class"])
        ) {
            $this->classAnnotations = new Collection($this->reflectionData["class"]);
        }

        return $this->classAnnotations;
    }

    /**
     * Returns the annotations found in the constants' docblocks
     *
     * @return Collection[]
     */
    public function getConstantsAnnotations(): array
    {
        return $this->traverseCollection(
            "constants",
            "constantAnnotations"
        );
    }

    /**
     * Returns the annotations found in the properties' docblocks
     *
     * @return Collection[]
     */
    public function getPropertiesAnnotations(): array
    {
        return $this->traverseCollection(
            "properties",
            "propertyAnnotations"
        );
    }

    /**
     * Returns the annotations found in the methods' docblocks
     *
     * @return Collection[]
     */
    public function getMethodsAnnotations(): array
    {
        return $this->traverseCollection(
            "methods",
            "methodAnnotations"
        );
    }

    /**
     * Returns the raw parsing intermediate definitions used to construct the
     * reflection
     *
     * @return array
     */
    public function getReflectionData(): array
    {
        return $this->reflectionData;
    }

    /**
     * @param string $element
     * @param string $collection
     *
     * @return array
     */
    private function traverseCollection(string $element, string $collection): array
    {
        $reflectionArray = $this->reflectionData[$element] ?? null;
        if (
            is_array($reflectionArray) &&
            true !== empty($reflectionArray)
        ) {
            foreach ($reflectionArray as $key => $data) {
                $this->{$collection}[$key] = new Collection($data);
            }
        }

        return $this->{$collection};
    }
}
