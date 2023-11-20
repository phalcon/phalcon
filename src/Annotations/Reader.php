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

use Phalcon\Parsers\Parser;
use ReflectionClass;

use ReflectionException;

use function array_keys;
use function is_array;
use function is_string;

/**
 * Parses docblocks returning an array with the found annotations
 */
class Reader implements ReaderInterface
{
    /**
     * Reads annotations from the class docblocks, its methods and/or properties
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
         * A ReflectionClass is used to obtain the class docblock
         */
        $reflection = new ReflectionClass($className);

        $comment = $reflection->getDocComment();
        if (false !== $comment) {
            /**
             * Read annotations from class
             */
            $classAnnotations = Parser::annotationsParse(
                $comment,
                $reflection->getFileName(),
                $reflection->getStartLine()
            );

            /**
             * Append the class annotations to the annotations var
             */
            if (is_array($classAnnotations)) {
                $annotations["class"] = $classAnnotations;
            }
        }

        /**
         * Get class constants
         */
        $constants = $reflection->getConstants();
        if (true !== empty($constants)) {
            /**
             * Line declaration for constants isn't available
             */
            $line                = 1;
            $arrayKeys           = array_keys($constants);
            $anotationsConstants = [];
            foreach ($arrayKeys as $constant) {
                /**
                 * Read comment from constant docblock
                 */
                $constantReflection = $reflection->getReflectionConstant(
                    $constant
                );
                $comment            = $constantReflection->getDocComment();
                if (false !== $comment) {
                    /**
                     * Parse constant docblock comment
                     */
                    $constantAnnotations = Parser::annotationsParse(
                        $comment,
                        $reflection->getFileName(),
                        $line
                    );

                    if (is_array($constantAnnotations)) {
                        $anotationsConstants[$constant] = $constantAnnotations;
                    }
                }
            }

            if (true !== empty($anotationsConstants)) {
                $annotations["constants"] = $anotationsConstants;
            }
        }

        /**
         * Get the class properties
         */
        $properties = $reflection->getProperties();
        if (true !== empty($properties)) {
            /**
             * Line declaration for properties isn't available
             */
            $line                  = 1;
            $annotationsProperties = [];
            foreach ($properties as $property) {
                /**
                 * Read comment from property
                 */
                $comment = $property->getDocComment();
                if (false !== $comment) {
                    /**
                     * Parse property docblock comment
                     */
                    $propertyAnnotations = Parser::annotationsParse(
                        $comment,
                        $reflection->getFileName(),
                        $line
                    );

                    if (is_array($propertyAnnotations)) {
                        $annotationsProperties[$property->name] = $propertyAnnotations;
                    }
                }
            }

            if (true !== empty($annotationsProperties)) {
                $annotations["properties"] = $annotationsProperties;
            }
        }

        /**
         * Get the class methods
         */
        $methods = $reflection->getMethods();
        if (true !== empty($methods)) {
            $annotationsMethods = [];
            foreach ($methods as $method) {
                /**
                 * Read comment from method
                 */
                $comment = $method->getDocComment();
                if (false !== $comment) {
                    /**
                     * Parse method docblock comment
                     */
                    $methodAnnotations = Parser::annotationsParse(
                        $comment,
                        $method->getFileName(),
                        $method->getStartLine()
                    );

                    if (is_array($methodAnnotations)) {
                        $annotationsMethods[$method->name] = $methodAnnotations;
                    }
                }
            }

            if (true !== empty($annotationsMethods)) {
                $annotations["methods"] = $annotationsMethods;
            }
        }

        return $annotations;
    }

    /**
     * Parses a raw doc block returning the annotations found
     *
     * @param string     $docBlock
     * @param mixed|null $file
     * @param mixed|null $line
     *
     * @return array
     */
    public static function parseDocBlock(
        string $docBlock,
        mixed $file = null,
        mixed $line = null
    ): array {
        if (true !== is_string($file)) {
            $file = "eval code";
        }

        return Parser::annotationsParse($docBlock, $file, $line);
    }
}
