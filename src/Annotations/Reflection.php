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

use Phalcon\Contracts\Annotations\AnnotationsTypes;

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
     * @phpstan-var annotations_reflection_data
     */
    protected array $reflectionData = [];

    /**
     * @phpstan-param annotations_reflection_data $reflectionData
     */
    public function __construct(array $reflectionData = [])
    {
        $this->reflectionData = $reflectionData;
    }

    /**
     * Returns the annotations found in the class docblock
     */
    public function getClassAnnotations(): Collection | null
    {
        if (null === $this->classAnnotations) {
            if (isset($this->reflectionData["class"])) {
                $this->classAnnotations = new Collection(
                    $this->reflectionData["class"]
                );
            }
        }

        return $this->classAnnotations;
    }

    /**
     * Returns the annotations found in the constants' docblocks
     *
     * @return Collection[]
     *
     * @phpstan-return annotations_collection_map
     */
    public function getConstantsAnnotations(): array
    {
        if (isset($this->reflectionData["constants"])) {
            $reflectionConstants = $this->reflectionData["constants"];

            if (is_array($reflectionConstants) && !empty($reflectionConstants)) {
                foreach ($reflectionConstants as $constant => $reflectionConstant) {
                    $this->constantAnnotations[$constant] = new Collection(
                        $reflectionConstant
                    );
                }
            }
        }

        return $this->constantAnnotations;
    }

    /**
     * Returns the annotations found in the methods' docblocks
     *
     * @return Collection[]
     *
     * @phpstan-return annotations_collection_map
     */
    public function getMethodsAnnotations(): array
    {
        if (isset($this->reflectionData["methods"])) {
            $reflectionMethods = $this->reflectionData["methods"];

            if (is_array($reflectionMethods) && !empty($reflectionMethods)) {
                foreach ($reflectionMethods as $methodName => $reflectionMethod) {
                    $this->methodAnnotations[$methodName] = new Collection(
                        $reflectionMethod
                    );
                }
            }
        }

        return $this->methodAnnotations;
    }

    /**
     * Returns the annotations found in the properties' docblocks
     *
     * @return Collection[]
     *
     * @phpstan-return annotations_collection_map
     */
    public function getPropertiesAnnotations(): array
    {
        if (isset($this->reflectionData["properties"])) {
            $reflectionProperties = $this->reflectionData["properties"];

            if (is_array($reflectionProperties) && !empty($reflectionProperties)) {
                foreach ($reflectionProperties as $property => $reflectionProperty) {
                    $this->propertyAnnotations[$property] = new Collection(
                        $reflectionProperty
                    );
                }
            }
        }

        return $this->propertyAnnotations;
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
}
