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

use Phalcon\Contracts\Annotations\AnnotationsTypes;

use function is_array;

/**
 * Allows to manipulate the annotations reflection in an OO manner
 *
 *```php
 * use Phalcon\Components\Annotations\Reader;
 * use Phalcon\Components\Annotations\Reflection;
 *
 * // Parse the annotations in a class
 * $reader = new Reader();
 * $parsing = $reader->parse("MyComponent");
 *
 * // Create the reflection
 * $reflection = new Reflection($parsing);
 *
 * // Get the annotations from the class
 * $classAnnotations = $reflection->getClassAnnotations();
 *```
 *
 * @phpstan-import-type annotations_collection_map from AnnotationsTypes
 * @phpstan-import-type annotations_reflection_data from AnnotationsTypes
 */
class Reflection
{
    protected Collection | null $classAnnotations = null;

    /**
     * @phpstan-var annotations_collection_map
     */
    protected array $constantAnnotations = [];

    /**
     * @phpstan-var annotations_collection_map
     */
    protected array $methodAnnotations = [];

    /**
     * @phpstan-var annotations_collection_map
     */
    protected array $propertyAnnotations = [];

    /**
     * Constructor
     *
     * @phpstan-param annotations_reflection_data $reflectionData
     */
    public function __construct(
        protected array $reflectionData = []
    ) {
    }

    /**
     * Returns the annotations found in the class docblock
     */
    public function getClassAnnotations(): Collection | null
    {
        if (
            null === $this->classAnnotations &&
            isset($this->reflectionData["class"])
        ) {
            $this->classAnnotations = $this->reflectionData["class"];
        }

        return $this->classAnnotations;
    }

    /**
     * Returns the annotations found as constants
     *
     * @phpstan-return annotations_collection_map
     */
    public function getConstantsAnnotations(): array
    {
        return $this->traverseCollection(
            "constants",
            "constantAnnotations"
        );
    }

    /**
     * Returns the annotations found at methods
     *
     * @phpstan-return annotations_collection_map
     */
    public function getMethodsAnnotations(): array
    {
        return $this->traverseCollection(
            "methods",
            "methodAnnotations"
        );
    }

    /**
     * Returns the annotations found at properties
     *
     * @phpstan-return annotations_collection_map
     */
    public function getPropertiesAnnotations(): array
    {
        return $this->traverseCollection(
            "properties",
            "propertyAnnotations"
        );
    }

    /**
     * Returns the raw parsing intermediate definitions used to construct the
     * reflection
     *
     * @phpstan-return annotations_reflection_data
     */
    public function getReflectionData(): array
    {
        return $this->reflectionData;
    }

    /**
     * @phpstan-return annotations_collection_map
     */
    private function traverseCollection(string $element, string $collection): array
    {
        /** @phpstan-var annotations_collection_map $stored */
        $stored          = $this->{$collection};
        $reflectionArray = $this->reflectionData[$element] ?? null;

        if (
            is_array($reflectionArray) &&
            !empty($reflectionArray)
        ) {
            foreach ($reflectionArray as $key => $data) {
                $stored[$key] = $data;
            }

            $this->{$collection} = $stored;
        }

        return $stored;
    }
}
