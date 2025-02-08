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
         * Get the class properties
         */
        $properties            = $reflection->getProperties();
        $annotationsProperties = [];
        foreach ($properties as $property) {
            $propertyAttributes = $property->getAttributes();
            if (count($propertyAttributes) > 0) {
                $annotationsProperties[$property->getName()] = new Collection($propertyAttributes);
            }
        }

        if (count($annotationsProperties) !== 0) {
            $attributes["properties"] = $annotationsProperties;
        }

        /**
         * Get the class methods
         */
        $methods            = $reflection->getMethods();
        $annotationsMethods = [];
        foreach ($methods as $method) {
            $methodAttributes = $method->getAttributes();
            if (count($methodAttributes) > 0) {
                $annotationsMethods[$method->getName()] = new Collection($methodAttributes);
            }
        }

        if (count($annotationsMethods) !== 0) {
            $attributes["methods"] = $annotationsMethods;
        }

        return $attributes;
    }
}
