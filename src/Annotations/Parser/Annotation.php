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

use ReflectionAttribute;

use function count;
use function ltrim;
use function strrchr;

/**
 * Represents a single attribute in an attributes collection
 */
class Annotation
{
    /**
     * Attribute Arguments
     *
     * @var array
     */
    protected array $arguments = [];

    /**
     * Attribute Name
     *
     * @var string
     */
    protected string $name;

    /**
     * Constructor
     *
     * @param ReflectionAttribute $reflectionData
     */
    public function __construct(ReflectionAttribute $reflectionData)
    {
        $this->name      = ltrim(strrchr($reflectionData->getName() ?: "", '\\'), '\\');
        $this->arguments = $reflectionData->getArguments() ?? [];
    }

    /**
     * Returns an argument in a specific position
     *
     * @param int | string $position
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
     * Returns the attribute's base name
     *
     * @return string
     */
    public function getCleanName(): string
    {
        return ltrim(strrchr($this->name, '\\'), '\\');
    }

    /**
     * Returns the attribute's name
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
     * @param int | string $position
     *
     * @return bool
     */
    public function hasArgument(int | string $position): bool
    {
        return isset($this->arguments[$position]);
    }

    /**
     * Returns the number of arguments that the attribute has
     *
     * @return int
     */
    public function numberArguments(): int
    {
        return count($this->arguments);
    }
}
