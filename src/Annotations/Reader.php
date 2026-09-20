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

use Phalcon\Annotations\Docblock\Exception as DocblockException;
use Phalcon\Annotations\Docblock\Parser\Parser;
use Phalcon\Contracts\Annotations\AnnotationsTypes;
use ReflectionClass;
use ReflectionClassConstant;

use function array_keys;
use function is_array;
use function is_int;
use function is_string;

/**
 * Parses docblocks returning an array with the found annotations
 *
 * @phpstan-import-type annotations_node_list from AnnotationsTypes
 * @phpstan-import-type annotations_reflection_data from AnnotationsTypes
 */
class Reader implements ReaderInterface
{
    /**
     * Parses a raw doc block returning the annotations found
     *
     * @throws Exception
     *
     * @phpstan-param string|null $file
     * @phpstan-param int|null    $line
     *
     * @phpstan-return annotations_node_list|false
     */
    public static function parseDocBlock(
        string $docBlock,
        mixed $file = null,
        mixed $line = null
    ): array | false {
        if (!is_string($file)) {
            $file = "eval code";
        }

        return self::parseAnnotations($docBlock, $file, $line);
    }

    /**
     * Does the work of phannot_parse_annotations() in the C extension. The
     * parser of the phalcon/annotations library parses the docblock. A file
     * that is not a string becomes "eval" and a line that is not an integer
     * becomes 0, the same as in the C extension. A parser error becomes a
     * Phalcon\Annotations\Exception.
     *
     * @throws Exception
     *
     * @phpstan-return annotations_node_list|false
     */
    private static function parseAnnotations(
        string $comment,
        mixed $file,
        mixed $line
    ): array | false {
        try {
            /** @var annotations_node_list|false */
            return (new Parser())->parse(
                $comment,
                is_string($file) ? $file : "eval",
                is_int($line) ? $line : 0
            );
        } catch (DocblockException $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    /**
     * Reads annotations from the class docblocks, its methods and/or properties
     *
     * @throws Exception
     * @throws \ReflectionException
     *
     * @phpstan-param class-string $className
     *
     * @phpstan-return annotations_reflection_data
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
            $classAnnotations = self::parseAnnotations(
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

        if (!empty($constants)) {
            /**
             * Line declaration for constants isn't available
             */
            $line                 = 1;
            $arrayKeys            = array_keys($constants);
            $annotationsConstants = [];

            foreach ($arrayKeys as $constant) {
                /**
                 * Read comment from constant docblock
                 */
                /**
                 * The name comes from getConstants(), so the constant exists.
                 *
                 * @var ReflectionClassConstant $constantReflection
                 */
                $constantReflection = $reflection->getReflectionConstant($constant);
                $comment            = $constantReflection->getDocComment();
                if (false !== $comment) {
                    /**
                     * Parse constant docblock comment
                     */
                    $constantAnnotations = self::parseAnnotations(
                        $comment,
                        $reflection->getFileName(),
                        $line
                    );

                    if (is_array($constantAnnotations)) {
                        $annotationsConstants[$constant] = $constantAnnotations;
                    }
                }
            }

            if (!empty($annotationsConstants)) {
                $annotations["constants"] = $annotationsConstants;
            }
        }

        /**
         * Get the class properties
         */
        $properties = $reflection->getProperties();

        if (!empty($properties)) {
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
                    $propertyAnnotations = self::parseAnnotations(
                        $comment,
                        $reflection->getFileName(),
                        $line
                    );

                    if (is_array($propertyAnnotations)) {
                        $annotationsProperties[$property->name] = $propertyAnnotations;
                    }
                }
            }

            if (!empty($annotationsProperties)) {
                $annotations["properties"] = $annotationsProperties;
            }
        }

        /**
         * Get the class methods
         */
        $methods = $reflection->getMethods();

        if (false === empty($methods)) {
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
                    $methodAnnotations = self::parseAnnotations(
                        $comment,
                        $method->getFileName(),
                        $method->getStartLine()
                    );

                    if (is_array($methodAnnotations)) {
                        $annotationsMethods[$method->name] = $methodAnnotations;
                    }
                }
            }

            if (!empty($annotationsMethods)) {
                $annotations["methods"] = $annotationsMethods;
            }
        }

        return $annotations;
    }
}
