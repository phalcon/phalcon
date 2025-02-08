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

use function count;
use function is_array;

/**
 * Allows to manipulate the attributes reflection in an OO manner
 *
 *```php
 * use Phalcon\Components\Attributes\Reader;
 * use Phalcon\Components\Attributes\Reflection;
 *
 * // Parse the attributes in a class
 * $reader = new Reader();
 * $parsing = $reader->parse("MyComponent");
 *
 * // Create the reflection
 * $reflection = new Reflection($parsing);
 *
 * // Get the attributes from the class
 * $classAttributes = $reflection->getClassAttributes();
 *```
 */
class Reflection
{
    /**
     * @var Collection|null
     */
    protected ?Collection $classAttributes = null;

    /**
     * @var array
     */
    protected array $constantAttributes = [];

    /**
     * @var array
     */
    protected array $methodAttributes = [];

    /**
     * @var array
     */
    protected array $propertyAttributes = [];

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
     * Returns the attributes found in the class docblock
     *
     * @return Collection|null
     */
    public function getClassAttributes(): Collection | null
    {
        if (
            null === $this->classAttributes &&
            isset($this->reflectionData["class"])
        ) {
            $this->classAttributes = $this->reflectionData["class"];
        }

        return $this->classAttributes;
    }

    /**
     * Returns the attributes found as constants
     *
     * @return Collection[]
     */
    public function getConstantsAttributes(): array
    {
        return $this->traverseCollection(
            "constants",
            "constantAttributes"
        );
    }

    /**
     * Returns the attributes found at methods
     *
     * @return Collection[]
     */
    public function getMethodsAttributes(): array
    {
        return $this->traverseCollection(
            "methods",
            "methodAttributes"
        );
    }

    /**
     * Returns the attributes found at properties
     *
     * @return Collection[]
     */
    public function getPropertiesAttributes(): array
    {
        return $this->traverseCollection(
            "properties",
            "propertyAttributes"
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
