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

namespace Phalcon\Container;

use Phalcon\Container\Definitions\AbstractDefinition;
use Phalcon\Container\Definitions\Definitions;
use Phalcon\Container\Interfaces\ProviderInterface;
use Phalcon\Container\Traits\ArgumentsTrait;
use Psr\Container\ContainerInterface;
use ReflectionClass;

use function class_exists;
use function interface_exists;

/**
 * Dependency injection container.
 */
class Container implements ContainerInterface
{
    use ArgumentsTrait;

    /**
     * @var array
     */
    protected array $has = [];

    /**
     * @var array
     */
    protected array $registry = [];

    /**
     * @param Definitions         $definitions
     * @param ProviderInterface[] $providers
     */
    public function __construct(
        protected Definitions $definitions,
        iterable $providers = []
    ) {
        foreach ($providers as $provider) {
            $provider->provide($this->definitions);
        }

        $this->registry[static::class] = $this;
    }

    public function callableGet(string $id): callable
    {
        return function () use ($id) {
            return $this->get($id);
        };
    }

    public function callableNew(string $id): callable
    {
        return function () use ($id) {
            return $this->new($id);
        };
    }

    public function get(string $id): mixed
    {
        if (!isset($this->registry[$id])) {
            $this->registry[$id] = $this->new($id);
        }

        return $this->registry[$id];
    }

    public function has(string $id): bool
    {
        if (!isset($this->has[$id])) {
            $this->has[$id] = $this->find($id);
        }

        return $this->has[$id];
    }

    public function new(string $id): mixed
    {
        return $this->resolveArgument($this, $this->definitions->$id);
    }

    protected function find(string $id): bool
    {
        if (!isset($this->definitions->$id)) {
            return $this->findImplicit($id);
        }

        if ($this->definitions->$id instanceof AbstractDefinition) {
            return $this->definitions->$id->isInstantiable($this);
        }

        return true;
    }

    protected function findImplicit(string $id): bool
    {
        if (!class_exists($id) && !interface_exists($id)) {
            return false;
        }

        $reflection = new ReflectionClass($id);
        return $reflection->isInstantiable();
    }
}
