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
use ReflectionClass;
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
     * @var array
     */
    protected array $boundModels = [];

    /**
     * Internal cache for caching parameters for model binding during request
     *
     * @var array
     */
    protected array $internalCache = [];

    /**
     * Array for original values
     *
     * @var array
     */
    protected array $originalValues = [];

    /**
     * @param AdapterInterface|null $cache
     */
    public function __construct(
        protected AdapterInterface | null $cache = null
    ) {
    }

    /**
     * Bind models into params in proper handler
     *
     * @param object      $handler
     * @param array       $params
     * @param string      $cacheKey
     * @param string|null $methodName
     *
     * @return array
     * @throws Exception
     * @throws ReflectionException
     */
    public function bindToHandler(
        object $handler,
        array $params,
        string $cacheKey,
        string | null $methodName = null
    ): array {
        $this->originalValues = [];

        if (!($handler instanceof Closure) && null === $methodName) {
            throw new Exception(
                "You must specify methodName for handler or pass Closure as handler"
            );
        }

        $this->boundModels = [];
        $paramsCache       = $this->getParamsFromCache($cacheKey);

        if (is_array($paramsCache)) {
            foreach ($paramsCache as $paramKey => $className) {
                $paramValue                      = $params[$paramKey];
                $boundModel                      = $this->findBoundModel($paramValue, $className);
                $this->originalValues[$paramKey] = $paramValue;
                $params[$paramKey]               = $boundModel;
                $this->boundModels[$paramKey]    = $boundModel;
            }

            return $params;
        }

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
     * @return array
     */
    public function getBoundModels(): array
    {
        return $this->boundModels;
    }

    /**
     * Sets cache instance
     *
     * @return AdapterInterface
     */
    public function getCache(): AdapterInterface
    {
        return $this->cache;
    }

    /**
     * Return the array for original values
     *
     * @return array
     */
    public function getOriginalValues(): array
    {
        return $this->originalValues;
    }

    /**
     * Gets cache instance
     *
     * @param AdapterInterface $cache
     *
     * @return BinderInterface
     */
    public function setCache(AdapterInterface $cache): BinderInterface
    {
        $this->cache = $cache;

        return $this;
    }

    /**
     * Find the model by param value.
     *
     * @param mixed  $paramValue
     * @param string $className
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
     * @param string $cacheKey
     *
     * @return array|null
     */
    protected function getParamsFromCache(string $cacheKey): array | null
    {
        if (isset($this->internalCache[$cacheKey])) {
            return $this->internalCache[$cacheKey];
        }

        if (null === $this->cache || true !== $this->cache->has($cacheKey)) {
            return null;
        }

        $internalParams                 = $this->cache->get($cacheKey);
        $this->internalCache[$cacheKey] = $internalParams;

        return $internalParams;
    }

    /**
     * Get modified params for handler using reflection
     *
     * @param object $handler
     * @param array  $params
     * @param string $cacheKey
     * @param string $methodName
     *
     * @return array
     * @throws Exception
     * @throws ReflectionException
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
            $reflection = new ReflectionFunction($handler);
        }

        $methodParams = $reflection->getParameters();
        $paramsKeys   = array_keys($params);

        foreach ($methodParams as $paramKey => $methodParam) {
            $reflectionType = $methodParam->getType();
            if (null === $reflectionType) {
                continue;
            }
            /** @var ReflectionNamedType $typeClassName */
            $typeClassName   = $reflectionType->getName();
            $reflectionClass = new ReflectionClass($typeClassName);
            $className       = $reflectionClass->getName();

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
                        throw new Exception(
                            "Handler must implement "
                            . "Phalcon\Mvc\Model\Binder\BindableInterface in "
                            . "order to use Phalcon\Mvc\Model as parameter"
                        );
                    }
                }

                if (is_array($realClasses)) {
                    if (!isset($realClasses[$paramKey])) {
                        throw new Exception(
                            "You should provide model class name for "
                            . $paramKey
                            . " parameter"
                        );
                    }
                    $className  = $realClasses[$paramKey];
                    $boundModel = $this->findBoundModel($paramValue, $className);
                } elseif (is_string($realClasses)) {
                    $className  = $realClasses;
                    $boundModel = $this->findBoundModel($paramValue, $className);
                } else {
                    throw new Exception(
                        "getModelName should return array or string"
                    );
                }
            } elseif (is_subclass_of($className, "Phalcon\Mvc\Model")) {
                $boundModel = $this->findBoundModel($paramValue, $className);
            }

            if (null !== $boundModel) {
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
