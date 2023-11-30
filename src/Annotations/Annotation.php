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

use Phalcon\Parsers\Enum as AnnEnum;

/**
 * Represents a single annotation in an annotations collection
 */
class Annotation
{
    /**
     * Annotation Arguments
     *
     * @var array
     */
    protected array $arguments = [];

    /**
     * Annotation ExprArguments
     *
     * @var array
     */
    protected array $exprArguments = [];

    /**
     * Annotation Name
     *
     * @var string
     */
    protected string $name;

    /**
     * Constructor
     *
     * @param array $reflectionData
     *
     * @throws Exception
     */
    public function __construct(array $reflectionData)
    {
        $this->name = $reflectionData["name"] ?? "";

        /**
         * Process annotation arguments
         */
        if (isset($reflectionData["arguments"])) {
            $exprArguments = $reflectionData["arguments"];
            $arguments     = [];
            foreach ($exprArguments as $argument) {
                $resolvedArgument = $this->getExpression(
                    $argument["expr"]
                );

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
     * @param int|string $position
     *
     * @return mixed
     */
    public function getArgument(int | string $position): mixed
    {
        return $this->arguments[$position] ?? null;
    }

    /**
     * Returns the expression arguments
     *
     * @return array
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * Returns the expression arguments without resolving
     *
     * @return array
     */
    public function getExprArguments(): array
    {
        return $this->exprArguments;
    }

    /**
     * Resolves an annotation expression
     *
     * @param array $expr
     *
     * @return mixed
     * @throws Exception
     */
    public function getExpression(array $expr): mixed
    {
        $type = $expr["type"];

        switch ($type) {
            case AnnEnum::PHANNOT_T_INTEGER:
            case AnnEnum::PHANNOT_T_DOUBLE:
            case AnnEnum::PHANNOT_T_STRING:
            case AnnEnum::PHANNOT_T_IDENTIFIER:
                $value = $expr["value"];
                break;

            case AnnEnum::PHANNOT_T_NULL:
                $value = null;
                break;

            case AnnEnum::PHANNOT_T_FALSE:
                $value = false;
                break;

            case AnnEnum::PHANNOT_T_TRUE:
                $value = true;
                break;

            case AnnEnum::PHANNOT_T_ARRAY:
                $arrayValue = [];
                foreach ($expr["items"] as $item) {
                    $resolvedItem = $this->getExpression($item["expr"]);

                    if (isset($item["name"])) {
                        $arrayValue[$item["name"]] = $resolvedItem;
                    } else {
                        $arrayValue[] = $resolvedItem;
                    }
                }

                return $arrayValue;

            case AnnEnum::PHANNOT_T_ANNOTATION:
                return new Annotation($expr);

            default:
                throw new Exception("The expression " . $type . " is unknown");
        }

        return $value;
    }

    /**
     * Returns the annotation's name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Returns a named argument
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getNamedArgument(string $name): mixed
    {
        return $this->arguments[$name] ?? null;
    }

    /**
     * Returns a named parameter
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getNamedParameter(string $name): mixed
    {
        return $this->getNamedArgument($name);
    }

    /**
     * Returns an argument in a specific position
     *
     * @param int|string $position
     *
     * @return bool
     */
    public function hasArgument(int | string $position): bool
    {
        return isset($this->arguments[$position]);
    }

    /**
     * Returns the number of arguments that the annotation has
     *
     * @return int
     */
    public function numberArguments(): int
    {
        return count($this->arguments);
    }
}
