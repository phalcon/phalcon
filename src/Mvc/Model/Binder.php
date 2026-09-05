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

namespace Phalcon\Mvc\Model;

use Closure;
use Phalcon\Cache\Adapter\AdapterInterface;
use Phalcon\Mvc\Controller\BindModelInterface;
use Phalcon\Mvc\Model\Binder\BindableInterface;
use Phalcon\Mvc\Model\Exceptions\HandlerMustImplementBindable;
use Phalcon\Mvc\Model\Exceptions\InvalidGetModelNameReturn;
use Phalcon\Mvc\Model\Exceptions\MissingMethodName;
use Phalcon\Mvc\Model\Exceptions\MissingModelClassName;
use Phalcon\Mvc\ModelInterface;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionNamedType;

use function array_keys;
use function get_class;
use function is_array;
use function is_string;
use function is_subclass_of;

/**
 * This is a class for binding models into params for handler
 */
class Binder implements BinderInterface
{
    /**
     * Array for storing active bound models
     *
     * @phpstan-var array<array-key, ModelInterface>
     */
    protected array $boundModels = [];

    /**
     * Internal cache for caching parameters for model binding during request
     *
     * @phpstan-var array<string, array<array-key, string>>
     */
    protected array $internalCache = [];

    /**
     * Array for original values
     *
     * @phpstan-var array<array-key, mixed>
     */
    protected array $originalValues = [];

    /**
     * Phalcon\Mvc\Model\Binder constructor
     */
    public function __construct(
        protected AdapterInterface | null $cache = null
    ) {
    }

    /**
     * Bind models into params in proper handler
     *
     * @throws Exception
     * @throws ReflectionException
     *
     * @phpstan-param array<array-key, mixed> $params
     * @phpstan-return array<array-key, mixed>
     */
    public function bindToHandler(
        object $handler,
        array $params,
        string $cacheKey,
        string | null $methodName = null
    ): array {
        $this->originalValues = [];

        if (!($handler instanceof Closure) && null === $methodName) {
            throw new MissingMethodName();
        }

        $this->boundModels = [];
        $paramsCache       = $this->getParamsFromCache($cacheKey);

        if (is_array($paramsCache)) {
            foreach ($paramsCache as $paramKey => $className) {
                $paramValue = $params[$paramKey];
                /** @var ModelInterface $boundModel */
                $boundModel                      = $this->findBoundModel($paramValue, $className);
                $this->originalValues[$paramKey] = $paramValue;
                $params[$paramKey]               = $boundModel;
                $this->boundModels[$paramKey]    = $boundModel;
            }

            return $params;
        }

        /** @var string $methodName */
        return $this->getParamsFromReflection(
            $handler,
            $params,
            $cacheKey,
            $methodName
        );
    }

    /**
     * Return the active bound models
     *
     * @phpstan-return array<array-key, ModelInterface>
     */
    public function getBoundModels(): array
    {
        return $this->boundModels;
    }

    /**
     * Sets cache instance
     */
    public function getCache(): AdapterInterface
    {
        /** @var AdapterInterface $cache */
        $cache = $this->cache;

        return $cache;
    }

    /**
     * Return the array for original values
     *
     * @phpstan-return array<array-key, mixed>
     */
    public function getOriginalValues(): array
    {
        return $this->originalValues;
    }

    /**
     * Gets cache instance
     */
    public function setCache(AdapterInterface $cache): BinderInterface
    {
        $this->cache = $cache;

        return $this;
    }

    /**
     * Find the model by param value.
     *
     * @return mixed|null
     */
    protected function findBoundModel(mixed $paramValue, string $className)
    {
        return $className::findFirst($paramValue);
    }

    /**
     * Get params classes from cache by key
     *
     * @phpstan-return array<array-key, string>|null
     */
    protected function getParamsFromCache(string $cacheKey): array | null
    {
        if (isset($this->internalCache[$cacheKey])) {
            return $this->internalCache[$cacheKey];
        }

        if (null === $this->cache || true !== $this->cache->has($cacheKey)) {
            return null;
        }

        /** @var array<array-key, string> $internalParams */
        $internalParams                 = $this->cache->get($cacheKey);
        $this->internalCache[$cacheKey] = $internalParams;

        return $internalParams;
    }

    /**
     * Get modified params for handler using reflection
     *
     * @throws Exception
     * @throws ReflectionException
     *
     * @phpstan-param array<array-key, mixed> $params
     * @phpstan-return array<array-key, mixed>
     */
    protected function getParamsFromReflection(
        object $handler,
        array $params,
        string $cacheKey,
        string $methodName
    ): array {
        $paramsCache = [];
        $realClasses = null;

        if (null !== $methodName) {
            $reflection = new ReflectionMethod($handler, $methodName);
        } else {
            /**
             * bindToHandler() rejects a handler that is not a closure when
             * no method name is given.
             */
            /** @var Closure $handler */
            $reflection = new ReflectionFunction($handler);
        }

        $methodParams = $reflection->getParameters();
        $paramsKeys   = array_keys($params);

        foreach ($methodParams as $paramKey => $methodParam) {
            $reflectionClass = $methodParam->getType();
            if (!$reflectionClass || !($reflectionClass instanceof ReflectionNamedType)) {
                continue;
            }

            $className = $reflectionClass->getName();

            if (!isset($params[$paramKey])) {
                $paramKey = $paramsKeys[$paramKey];
            }

            $boundModel = null;
            $paramValue = $params[$paramKey];

            if ($className == "Phalcon\Mvc\Model") {
                if (null === $realClasses) {
                    if ($handler instanceof BindModelInterface) {
                        $handlerClass = get_class($handler);
                        $realClasses  = $handlerClass::getModelName();
                    } elseif ($handler instanceof BindableInterface) {
                        $realClasses = $handler->getModelName();
                    } else {
                        throw new HandlerMustImplementBindable();
                    }
                }

                if (is_array($realClasses)) {
                    if (!isset($realClasses[$paramKey])) {
                        throw new MissingModelClassName($paramKey);
                    }
                    $className  = $realClasses[$paramKey];
                    $boundModel = $this->findBoundModel($paramValue, $className);
                } elseif (is_string($realClasses)) {
                    $className  = $realClasses;
                    $boundModel = $this->findBoundModel($paramValue, $className);
                } else {
                    throw new InvalidGetModelNameReturn();
                }
            } elseif (is_subclass_of($className, "Phalcon\Mvc\Model")) {
                $boundModel = $this->findBoundModel($paramValue, $className);
            }

            if (null !== $boundModel) {
                /** @var ModelInterface $boundModel */
                $this->originalValues[$paramKey] = $paramValue;
                $params[$paramKey]               = $boundModel;
                $this->boundModels[$paramKey]    = $boundModel;
                $paramsCache[$paramKey]          = $className;
            }
        }

        $this->cache?->set($cacheKey, $paramsCache);

        $this->internalCache[$cacheKey] = $paramsCache;

        return $params;
    }
}
