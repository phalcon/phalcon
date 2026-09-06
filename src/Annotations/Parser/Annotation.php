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

use Phalcon\Contracts\Annotations\AnnotationsTypes;
use ReflectionAttribute;

use function count;
use function ltrim;
use function strrchr;

/**
 * Represents a single attribute in an attributes collection
 *
 * @phpstan-import-type annotations_arguments from AnnotationsTypes
 */
class Annotation
{
    /**
     * Attribute Arguments
     *
     * @phpstan-var annotations_arguments
     */
    protected array $arguments = [];

    /**
     * Attribute Name
     */
    protected string $name;

    /**
     * Constructor
     *
     * @param ReflectionAttribute<object> $reflectionData
     */
    public function __construct(ReflectionAttribute $reflectionData)
    {
        $name = $reflectionData->getName();

        $this->name      = ltrim(strrchr($name, '\\') ?: $name, '\\');
        $this->arguments = $reflectionData->getArguments();
    }

    /**
     * Returns an argument in a specific position
     */
    public function getArgument(int | string $position): mixed
    {
        return $this->arguments[$position] ?? null;
    }

    /**
     * Returns the expression arguments
     *
     * @phpstan-return annotations_arguments
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * Returns the attribute's base name
     */
    public function getCleanName(): string
    {
        return ltrim(strrchr($this->name, '\\') ?: $this->name, '\\');
    }

    /**
     * Returns the attribute's name
     */
    public function getName(): string
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
     */
    public function hasArgument(int | string $position): bool
    {
        return isset($this->arguments[$position]);
    }

    /**
     * Returns the number of arguments that the attribute has
     */
    public function numberArguments(): int
    {
        return count($this->arguments);
    }
}
