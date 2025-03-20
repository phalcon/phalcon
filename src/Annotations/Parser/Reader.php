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

use ReflectionClass;
use ReflectionException;

use function count;

/**
 * Parses classes returning an array with the found annotations
 */
class Reader implements ReaderInterface
{
    /**
     * Reads annotations from the class, its methods and/or properties
     *
     * @param string $className
     *
     * @return array
     * @throws ReflectionException
     */
    public function parse(string $className): array
    {
        $annotations = [];

        /**
         * A ReflectionClass is used to obtain the annotations.
         */
        $reflection = new ReflectionClass($className);

        $classAnnotations = $reflection->getAttributes();

        /**
         * Append the class annotations to the annotations var
         */
        if (count($classAnnotations) !== 0) {
            $annotations["class"] = new Collection($classAnnotations);
        }

        /**
         * Get class constants
         */
        $constants            = $reflection->getReflectionConstants();
        $annotationsConstants = [];
        foreach ($constants as $constant) {
            $constantAnnotations = $constant->getAttributes();
            if (count($constantAnnotations) > 0) {
                $annotationsConstants[$constant->getName()] = new Collection($constantAnnotations);
            }
        }

        if (!empty($annotationsConstants)) {
            $annotations["constants"] = $annotationsConstants;
        }

        /**
         * Get the class properties
         */
        $properties            = $reflection->getProperties();
        $annotationsProperties = [];
        foreach ($properties as $property) {
            $propertyAnnotations = $property->getAttributes();
            if (count($propertyAnnotations) > 0) {
                $annotationsProperties[$property->getName()] = new Collection($propertyAnnotations);
            }
        }

        if (count($annotationsProperties) !== 0) {
            $annotations["properties"] = $annotationsProperties;
        }

        /**
         * Get the class methods
         */
        $methods            = $reflection->getMethods();
        $annotationsMethods = [];
        foreach ($methods as $method) {
            $methodAnnotations = $method->getAttributes();
            if (count($methodAnnotations) > 0) {
                $annotationsMethods[$method->getName()] = new Collection($methodAnnotations);
            }
        }

        if (count($annotationsMethods) !== 0) {
            $annotations["methods"] = $annotationsMethods;
        }

        return $annotations;
    }
}
