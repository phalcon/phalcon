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
use Phalcon\Annotations\Exceptions\UnknownAnnotationExpression;
use Phalcon\Contracts\Annotations\AnnotationsTypes;

use function count;

/**
 * Represents a single annotation in an annotations collection
 *
 * @phpstan-import-type annotations_arguments from AnnotationsTypes
 * @phpstan-import-type annotations_expression from AnnotationsTypes
 * @phpstan-import-type annotations_node from AnnotationsTypes
 * @phpstan-import-type annotations_resolved_arguments from AnnotationsTypes
 */
class Annotation
{
    /**
     * Annotation Arguments
     *
     * @phpstan-var annotations_resolved_arguments
     */
    protected array $arguments = [];

    /**
     * Annotation ExprArguments
     *
     * @phpstan-var annotations_arguments
     */
    protected array $exprArguments = [];

    /**
     * Annotation Name
     */
    protected string | null $name = null;

    /**
     * Phalcon\Annotations\Annotation constructor
     *
     * @phpstan-param annotations_node $reflectionData
     */
    public function __construct(array $reflectionData)
    {
        if (isset($reflectionData["name"])) {
            $this->name = $reflectionData["name"];
        }

        /**
         * Process annotation arguments
         */
        if (isset($reflectionData["arguments"])) {
            $exprArguments = $reflectionData["arguments"];
            $arguments     = [];

            foreach ($exprArguments as $argument) {
                $resolvedArgument = $this->getExpression($argument["expr"]);

                if (isset($argument["name"])) {
                    $arguments[$argument["name"]] = $resolvedArgument;
                } else {
                    $arguments[] = $resolvedArgument;
                }
            }

            $this->arguments     = $arguments;
            $this->exprArguments = $exprArguments;
        }
    }

    /**
     * Returns an argument in a specific position
     *
     * @phpstan-param int|string $position
     */
    public function getArgument(int | string $position): mixed
    {
        return $this->arguments[$position] ?? null;
    }

    /**
     * Returns the expression arguments
     *
     * @phpstan-return annotations_resolved_arguments
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * Returns the expression arguments without resolving
     *
     * @phpstan-return annotations_arguments
     */
    public function getExprArguments(): array
    {
        return $this->exprArguments;
    }

    /**
     * Resolves an annotation expression
     *
     * @throws UnknownAnnotationExpression
     *
     * @phpstan-param annotations_expression $expr
     */
    public function getExpression(array $expr): mixed
    {
        $value = null;
        $type  = $expr["type"];

        switch ($type) {
            case Opcode::INTEGER->value:
            case Opcode::DOUBLE->value:
            case Opcode::STRING->value:
            case Opcode::IDENTIFIER->value:
                $value = $expr["value"];
                break;

            case Opcode::NULL->value:
                $value = null;
                break;

            case Opcode::FALSE->value:
                $value = false;
                break;

            case Opcode::TRUE->value:
                $value = true;
                break;

            case Opcode::ARRAY->value:
                $arrayValue = [];

                /**
                 * The parser gives a list of named items for an array.
                 *
                 * @var annotations_arguments $items
                 */
                $items = $expr["items"];
                foreach ($items as $item) {
                    $resolvedItem = $this->getExpression($item["expr"]);

                    if (isset($item["name"])) {
                        $arrayValue[$item["name"]] = $resolvedItem;
                    } else {
                        $arrayValue[] = $resolvedItem;
                    }
                }

                return $arrayValue;

            case Opcode::ANNOTATION->value:
                /** @var annotations_node $expr */
                return new Annotation($expr);

            default:
                /** @var int|string $type */
                throw new UnknownAnnotationExpression((string) $type);
        }

        return $value;
    }

    /**
     * Returns the annotation's name
     */
    public function getName(): string | null
    {
        return $this->name;
    }

    /**
     * Returns a named argument
     */
    public function getNamedArgument(string $name): mixed
    {
        return $this->arguments[$name] ?? null;
    }

    /**
     * Returns a named parameter
     */
    public function getNamedParameter(string $name): mixed
    {
        return $this->getNamedArgument($name);
    }

    /**
     * Returns an argument in a specific position
     *
     * @phpstan-param int|string $position
     */
    public function hasArgument(int | string $position): bool
    {
        return isset($this->arguments[$position]);
    }

    /**
     * Returns the number of arguments that the annotation has
     */
    public function numberArguments(): int
    {
        return count($this->arguments);
    }
}
