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

use ReflectionClass;
use ReflectionException;

use function count;

/**
 * Parses classes returning an array with the found attributes
 */
class Reader implements ReaderInterface
{
    /**
     * Reads attributes from the class, its methods and/or properties
     *
     * @param string $className
     *
     * @throws ReflectionException
     * @return array
     */
    public function parse(string $className): array
    {
        $attributes = [];

        /**
         * A ReflectionClass is used to obtain the attributes.
         */
        $reflection = new ReflectionClass($className);

        $classAttributes = $reflection->getAttributes();

        /**
         * Append the class attributes to the attributes var
         */
        if (count($classAttributes) !== 0) {
            $attributes["class"] = new Collection($classAttributes);
        }

        /**
         * Get class constants
         */
        $constants           = $reflection->getConstants();
        $attributesConstants = [];
        foreach ($constants as $constant) {
            $constantAttributes = $constant->getAttributes();
            if (count($constantAttributes) > 0) {
                $attributesConstants[$constant->getName()] = new Collection($constantAttributes);
            }
        }

        if (!empty($attributesConstants)) {
            $attributes["constants"] = $attributesConstants;
        }

        /**
         * Get the class properties
         */
        $properties           = $reflection->getProperties();
        $attributesProperties = [];
        foreach ($properties as $property) {
            $propertyAttributes = $property->getAttributes();
            if (count($propertyAttributes) > 0) {
                $attributesProperties[$property->getName()] = new Collection($propertyAttributes);
            }
        }

        if (count($attributesProperties) !== 0) {
            $attributes["properties"] = $attributesProperties;
        }

        /**
         * Get the class methods
         */
        $methods           = $reflection->getMethods();
        $attributesMethods = [];
        foreach ($methods as $method) {
            $methodAttributes = $method->getAttributes();
            if (count($methodAttributes) > 0) {
                $attributesMethods[$method->getName()] = new Collection($methodAttributes);
            }
        }

        if (count($attributesMethods) !== 0) {
            $attributes["methods"] = $attributesMethods;
        }

        return $attributes;
    }
}
