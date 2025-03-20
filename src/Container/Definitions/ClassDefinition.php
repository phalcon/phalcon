<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Implementation of this file has been influenced by CapsulePHP
 *
 * @link    https://github.com/capsulephp/di
 * @license https://github.com/capsulephp/di/blob/3.x/LICENSE.md
 */

declare(strict_types=1);

namespace Phalcon\Container\Definitions;

use Phalcon\Container\Container;
use Phalcon\Container\Exception;
use Phalcon\Container\Exception\NotAllowed;
use Phalcon\Container\Exception\NotDefined;
use Phalcon\Container\Exception\NotFound;
use Phalcon\Container\Lazy\Get;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;

use function array_key_exists;
use function array_pop;
use function class_exists;
use function end;
use function get_parent_class;
use function interface_exists;
use function is_array;

class ClassDefinition extends AbstractDefinition
{
    /**
     * @var array
     */
    protected array $arguments = [];

    /**
     * @var array
     */
    protected array $collatedArguments;

    /**
     * @var array
     */
    protected array $extenders = [];

    /**
     * @var ClassDefinition|null
     */
    protected ClassDefinition | null $inherit = null;

    /**
     * @var array
     */
    protected array $parameterNames = [];

    /**
     * @var array|ReflectionParameter[]
     */
    protected array $parameters = [];

    /**
     * @param string $id
     *
     * @throws NotFound
     */
    public function __construct(
        protected string $id
    ) {
        if (!class_exists($this->id)) {
            throw new NotFound("Class '$this->id' not found.");
        }

        $reflection           = new ReflectionClass($this->id);
        $this->isInstantiable = $reflection->isInstantiable();
        $constructor          = $reflection->getConstructor();

        if ($constructor === null) {
            return;
        }

        $this->parameters = $constructor->getParameters();

        foreach ($this->parameters as $i => $parameter) {
            $this->parameterNames[$parameter->getName()] = $i;
        }
    }

    /**
     * @param int|string $parameter
     * @param mixed      $argument
     *
     * @return $this
     */
    public function argument(int | string $parameter, mixed $argument): static
    {
        $position                   = $this->parameterNames[$parameter] ?? $parameter;
        $this->arguments[$position] = $argument;

        return $this;
    }

    /**
     * @param array $arguments
     *
     * @return $this
     */
    public function arguments(array $arguments): static
    {
        $this->arguments = [];

        foreach ($arguments as $parameter => $argument) {
            $this->argument($parameter, $argument);
        }

        return $this;
    }

    /**
     * @param string|null $class
     *
     * @return $this
     * @throws NotFound
     */
    public function class(string | null $class): static
    {
        if ($class === $this->id) {
            $class = null;
        }

        if ($class === null || class_exists($class)) {
            $this->class = $class;

            return $this;
        }

        throw new NotFound("Class '$class' not found.");
    }

    public function decorate(callable $callable): static
    {
        $this->extenders[] = [__FUNCTION__, $callable];

        return $this;
    }

    /**
     * @param int|string $parameter
     *
     * @return mixed
     */
    public function getArgument(int | string $parameter): mixed
    {
        $position = $this->parameterNames[$parameter] ?? $parameter;

        return $this->arguments[$position];
    }

    /**
     * @param int|string $parameter
     *
     * @return bool
     */
    public function hasArgument(int | string $parameter): bool
    {
        $position = $this->parameterNames[$parameter] ?? $parameter;

        return array_key_exists($position, $this->arguments);
    }

    /**
     * @param Definitions|null $definition
     *
     * @return $this
     */
    public function inherit(Definitions | null $definition): static
    {
        $parent = get_parent_class($this->id);

        if ($definition === null || $parent === false) {
            $this->inherit = null;
            return $this;
        }

        $this->inherit = $definition->$parent;

        return $this;
    }

    /**
     * @param string $method
     * @param mixed  ...$arguments
     *
     * @return $this
     */
    public function method(string $method, mixed ...$arguments): static
    {
        $this->extenders[] = [__FUNCTION__, [$method, $arguments]];

        return $this;
    }

    /**
     * @param callable $callable
     *
     * @return $this
     */
    public function modify(callable $callable): static
    {
        $this->extenders[] = [__FUNCTION__, $callable];

        return $this;
    }

    /**
     * @param Container $container
     *
     * @return object
     * @throws Exception\NotInstantiated
     */
    public function new(Container $container): object
    {
        $object = parent::new($container);

        return $this->applyExtenders($container, $object);
    }

    /**
     * @param string $name
     * @param mixed  $value
     *
     * @return $this
     */
    public function property(string $name, mixed $value): static
    {
        $this->extenders[] = [__FUNCTION__, [$name, $value]];

        return $this;
    }

    /**
     * @param Container $container
     * @param object    $object
     * @param array     $extender
     *
     * @return object
     */
    protected function applyExtender(
        Container $container,
        object $object,
        array $extender
    ): object {
        [$type, $spec] = $extender;

        switch ($type) {
            case 'decorate':
                $object = $spec($container, $object);
                break;

            case 'method':
                [$method, $arguments] = $spec;
                $object->$method(...$arguments);
                break;

            case 'modify':
                $spec($container, $object);
                break;

            case 'property':
                [$prop, $value] = $spec;
                $object->$prop = $this->resolveArgument($container, $value);
                break;
        }

        return $object;
    }

    /**
     * @param Container $container
     * @param object    $object
     *
     * @return object
     */
    protected function applyExtenders(Container $container, object $object): object
    {
        foreach ($this->extenders as $extender) {
            $object = $this->applyExtender($container, $object, $extender);
        }

        return $object;
    }

    /**
     * @param int                 $position
     * @param ReflectionParameter $parameter
     *
     * @return NotDefined
     */
    protected function argumentNotDefined(
        int $position,
        ReflectionParameter $parameter
    ): NotDefined {
        $name = $parameter->getName();
        /** @var ReflectionNamedType $type */
        $type = $parameter->getType();

        if ($type instanceof ReflectionUnionType) {
            return new NotDefined(
                "Union typed argument $position (\${$name}) "
                . "for class definition '{$this->id}' is not defined."
            );
        }

        $hint = $type->getName();

        if (
            $type->isBuiltin()
            || class_exists($hint)
            || interface_exists($hint)
        ) {
            return new NotDefined(
                'Required argument ' . $position
                . ' ($' . $name . ') for class definition \''
                . $this->id . '\' is not defined.'
            );
        }

        return new NotDefined(
            'Required argument ' . $position
            . ' ($' . $name . ') for class definition \''
            . $this->id . '\' is typehinted as '
            . $hint . ', which does not exist.'
        );
    }

    /**
     * @param Container $container
     *
     * @return void
     */
    protected function collateArguments(Container $container): void
    {
        $this->collatedArguments = [];

        $inherited = ($this->inherit === null)
            ? []
            : $this->inherit->getCollatedArguments($container);

        foreach ($this->parameters as $position => $parameter) {
            $this->collatePositionalArgument($position)
            || $this->collateTypedArgument($position, $parameter, $container)
            || $this->collateInheritedArgument($position, $inherited)
            || $this->collateOptionalArgument($position, $parameter);
        }
    }

    /**
     * @param int   $position
     * @param array $inherited
     *
     * @return bool
     */
    protected function collateInheritedArgument(
        int $position,
        array $inherited
    ): bool {
        if (array_key_exists($position, $inherited)) {
            $this->collatedArguments[$position] = $inherited[$position];
            return true;
        }

        return false;
    }

    /**
     * @param int                 $position
     * @param ReflectionParameter $parameter
     *
     * @return bool
     * @throws ReflectionException
     */
    protected function collateOptionalArgument(
        int $position,
        ReflectionParameter $parameter
    ): bool {
        if (!$parameter->isOptional()) {
            return false;
        }

        $value = $parameter->isVariadic()
            ? []
            : $parameter->getDefaultValue();

        $this->collatedArguments[$position] = $value;

        return true;
    }

    /**
     * @param int $position
     *
     * @return bool
     */
    protected function collatePositionalArgument(
        int $position
    ): bool {
        if (!array_key_exists($position, $this->arguments)) {
            return false;
        }

        $this->collatedArguments[$position] = $this->arguments[$position];

        return true;
    }

    /**
     * @param int                 $position
     * @param ReflectionParameter $parameter
     * @param Container           $container
     *
     * @return bool
     */
    protected function collateTypedArgument(
        int $position,
        ReflectionParameter $parameter,
        Container $container
    ): bool {
        $type = $parameter->getType();

        if ($type instanceof ReflectionNamedType) {
            $type = $type->getName();

            // explicit
            if (array_key_exists($type, $this->arguments)) {
                $this->collatedArguments[$position] = $this->arguments[$type];

                return true;
            }

            // implicit
            if ($container->has($type)) {
                $this->collatedArguments[$position] = new Get($type);

                return true;
            }
        }

        return false;
    }

    /**
     * @param array $arguments
     *
     * @return void
     * @throws NotAllowed
     */
    protected function expandVariadic(array &$arguments): void
    {
        $lastParameter = end($this->parameters);

        if ($lastParameter === false) {
            return;
        }

        if (!$lastParameter->isVariadic()) {
            return;
        }

        $lastArgument = end($arguments);

        if (!is_array($lastArgument)) {
            $type     = gettype($lastArgument);
            $position = $lastParameter->getPosition();
            $name     = $lastParameter->getName();

            throw new NotAllowed(
                "Variadic argument {$position} (\${$name}) "
                . "for class definition '{$this->id}' is defined as {$type}, "
                . "but should be an array of variadic values."
            );
        }

        $values = array_pop($arguments);

        foreach ($values as $value) {
            $arguments[] = $value;
        }
    }

    /**
     * @param Container $container
     *
     * @return array
     */
    protected function getCollatedArguments(Container $container): array
    {
        if (!isset($this->collatedArguments)) {
            $this->collateArguments($container);
        }

        return $this->collatedArguments;
    }

    /**
     * @param Container $container
     *
     * @return object
     * @throws NotAllowed
     * @throws NotDefined
     */
    protected function instantiate(Container $container): object
    {
        if ($this->factory !== null) {
            $factory = $this->resolveArgument($container, $this->factory);
            return $factory($container);
        }

        if ($this->class !== null) {
            return $container->new($this->class);
        }

        $arguments = $this->getCollatedArguments($container);

        foreach ($this->parameters as $position => $parameter) {
            if (!array_key_exists($position, $arguments)) {
                throw $this->argumentNotDefined($position, $parameter);
            }

            $arguments[$position] = $this->resolveArgument(
                $container,
                $arguments[$position]
            );
        }

        $this->expandVariadic($arguments);
        $class = $this->id;

        return new $class(...$arguments);
    }
}
