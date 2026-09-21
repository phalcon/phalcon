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

use Phalcon\Annotations\Docblock\Scanner\Opcode;
use Phalcon\Contracts\Annotations\AnnotationsTypes;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;

use function array_pop;
use function explode;
use function is_string;
use function str_starts_with;

/**
 * Parses PHP attributes returning an array with the found annotations
 *
 * The array has the same shape as the one of Phalcon\Annotations\Reader, so
 * the adapters, Reflection, Collection and Annotation do not know which
 * reader made it.
 *
 * PHP resolves the value of an attribute argument, so there is no parse tree
 * to walk. Each value goes in a node of the type Annotation::T_RESOLVED,
 * which Annotation::getExpression() gives back without a change.
 *
 * @phpstan-import-type annotations_arguments from AnnotationsTypes
 * @phpstan-import-type annotations_node_list from AnnotationsTypes
 * @phpstan-import-type annotations_reflection_data from AnnotationsTypes
 */
class AttributesReader implements ReaderInterface
{
    /**
     * An attribute of this namespace gets the short name, so that `#[Column]`
     * and `@Column` give the same name. Every other attribute keeps the full
     * class name, so that an attribute of another library cannot take the
     * place of a Phalcon one.
     */
    public const PHALCON_NAMESPACE = "Phalcon\\Annotations\\";

    /**
     * Reads attributes from the class, its constants, properties and methods
     *
     * @throws ReflectionException
     *
     * @phpstan-param class-string $className
     *
     * @phpstan-return annotations_reflection_data
     */
    public function parse(string $className): array
    {
        $annotations = [];
        $reflection  = new ReflectionClass($className);
        $file        = $reflection->getFileName();

        /**
         * An internal class has no file
         */
        if (!is_string($file)) {
            $file = "eval code";
        }

        /**
         * Read the attributes of the class
         */
        $classAttributes = $this->buildNodes(
            $reflection->getAttributes(),
            $file,
            (int) $reflection->getStartLine()
        );

        if (!empty($classAttributes)) {
            $annotations["class"] = $classAttributes;
        }

        /**
         * Read the attributes of the constants. A constant has no line, the
         * same as in the docblock reader.
         */
        $annotationsConstants = [];

        foreach ($reflection->getReflectionConstants() as $constant) {
            $constantAttributes = $this->buildNodes(
                $constant->getAttributes(),
                $file,
                1
            );

            if (!empty($constantAttributes)) {
                $annotationsConstants[$constant->getName()] = $constantAttributes;
            }
        }

        if (!empty($annotationsConstants)) {
            $annotations["constants"] = $annotationsConstants;
        }

        /**
         * Read the attributes of the properties. A property has no line.
         */
        $annotationsProperties = [];

        foreach ($reflection->getProperties() as $property) {
            $propertyAttributes = $this->buildNodes(
                $property->getAttributes(),
                $file,
                1
            );

            if (!empty($propertyAttributes)) {
                $annotationsProperties[$property->name] = $propertyAttributes;
            }
        }

        if (!empty($annotationsProperties)) {
            $annotations["properties"] = $annotationsProperties;
        }

        /**
         * Read the attributes of the methods
         */
        $annotationsMethods = [];

        foreach ($reflection->getMethods() as $method) {
            $methodFile = $method->getFileName();

            if (!is_string($methodFile)) {
                $methodFile = "eval code";
            }

            $methodAttributes = $this->buildNodes(
                $method->getAttributes(),
                $methodFile,
                (int) $method->getStartLine()
            );

            if (!empty($methodAttributes)) {
                $annotationsMethods[$method->name] = $methodAttributes;
            }
        }

        if (!empty($annotationsMethods)) {
            $annotations["methods"] = $annotationsMethods;
        }

        return $annotations;
    }

    /**
     * Makes the argument list of one attribute. PHP resolved the values
     * already, so each one goes in a node that Annotation::getExpression()
     * gives back without a change. An integer key is a positional argument
     * and a string key is a named one.
     *
     * @phpstan-param array<array-key, mixed> $attributeArguments
     *
     * @phpstan-return annotations_arguments
     */
    protected function buildArguments(array $attributeArguments): array
    {
        $arguments = [];

        foreach ($attributeArguments as $key => $value) {
            $argument = [
                "expr" => [
                    "type"  => Annotation::T_RESOLVED,
                    "value" => $value,
                ],
            ];

            if (is_string($key)) {
                $argument["name"] = $key;
            }

            $arguments[] = $argument;
        }

        return $arguments;
    }

    /**
     * Makes the node list of one target from its attributes
     *
     * @phpstan-param array<array-key, ReflectionAttribute<object>> $attributes
     *
     * @phpstan-return annotations_node_list
     */
    protected function buildNodes(
        array $attributes,
        string $file,
        int $line
    ): array {
        $nodes = [];

        foreach ($attributes as $attribute) {
            $name = $attribute->getName();

            if (str_starts_with($name, self::PHALCON_NAMESPACE)) {
                $parts = explode("\\", $name);
                $name  = (string) array_pop($parts);
            }

            /**
             * The node carries the same keys as the one the parser builds,
             * so that Reflection::getReflectionData() gives one shape
             * whichever reader filled it.
             */
            $node = [
                "type" => Opcode::ANNOTATION->value,
                "name" => $name,
                "file" => $file,
                "line" => $line,
            ];

            $attributeArguments = $attribute->getArguments();

            if (!empty($attributeArguments)) {
                $node["arguments"] = $this->buildArguments($attributeArguments);
            }

            $nodes[] = $node;
        }

        return $nodes;
    }
}
