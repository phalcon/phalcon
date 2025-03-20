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

use function count;
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
 */
class Reflection
{
    /**
     * @var Collection|null
     */
    protected Collection | null $classAnnotations = null;

    /**
     * @var array
     */
    protected array $constantAnnotations = [];

    /**
     * @var array
     */
    protected array $methodAnnotations = [];

    /**
     * @var array
     */
    protected array $propertyAnnotations = [];

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
     * Returns the annotations found at methods
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
     * Returns the annotations found at properties
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
            count($reflectionArray) !== 0
        ) {
            foreach ($reflectionArray as $key => $data) {
                $this->{$collection}[$key] = $data;
            }
        }

        return $this->{$collection};
    }
}
