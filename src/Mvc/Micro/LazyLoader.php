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

namespace Phalcon\Mvc\Micro;

use Phalcon\Mvc\Micro\Exceptions\LazyHandlerNotFound;
use Phalcon\Mvc\Model\BinderInterface;

use function array_values;

/**
 * Lazy-Load of handlers for Mvc\Micro using auto-loading
 */
class LazyLoader
{
    protected object | null $handler = null;

    /**
     * Phalcon\Mvc\Micro\LazyLoader constructor
     */
    public function __construct(
        protected string $definition
    ) {
    }

    /**
     * Calling __call method
     *
     * @return mixed
     * @throws Exception
     */
    public function callMethod(
        string $method,
        mixed $arguments,
        BinderInterface | null $modelBinder = null
    ) {
        $definition = $this->definition;

        if (null === $this->handler) {
            if (!class_exists($definition)) {
                throw new LazyHandlerNotFound($definition);
            }

            $this->handler = new $definition();
        }

        /**
         * The arguments are the call parameters of the method.
         */
        /** @var array<array-key, mixed> $params */
        $params = $arguments;

        if (null !== $modelBinder) {
            $bindCacheKey = "_PHMB_" . $definition . "_" . $method;
            $params       = $modelBinder->bindToHandler(
                $this->handler,
                $params,
                $bindCacheKey,
                $method
            );
        }

        /**
         * Call the handler
         */
        /** @var callable $callable */
        $callable = [$this->handler, $method];

        return call_user_func_array(
            $callable,
            array_values($params)
        );
    }

    public function getDefinition(): string
    {
        return $this->definition;
    }

    public function getHandler(): object | null
    {
        return $this->handler;
    }
}
