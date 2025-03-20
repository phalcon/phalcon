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

use Phalcon\Mvc\Model\BinderInterface;

use function array_values;

/**
 * Lazy-Load of handlers for Mvc\Micro using auto-loading
 */
class LazyLoader
{
    /**
     * @var object|null
     */
    protected object | null $handler = null;

    /**
     * Phalcon\Mvc\Micro\LazyLoader constructor
     *
     * @param string $definition
     */
    public function __construct(
        protected string $definition
    ) {
    }

    /**
     * Calling __call method
     *
     * @param string               $method
     * @param array                $arguments
     * @param BinderInterface|null $modelBinder
     *
     * @return mixed
     * @throws Exception
     */
    public function callMethod(
        string $method,
        array $arguments,
        BinderInterface | null $modelBinder = null
    ): mixed {
        $definition = $this->definition;

        if (null === $this->handler) {
            if (!class_exists($definition)) {
                throw new Exception(
                    "Handler '" . $definition . "' does not exist"
                );
            }

            $this->handler = new $definition();
        }

        if (null !== $modelBinder) {
            $bindCacheKey = "_PHMB_" . $definition . "_" . $method;
            $arguments    = $modelBinder->bindToHandler(
                $this->handler,
                $arguments,
                $bindCacheKey,
                $method
            );
        }

        /**
         * Call the handler
         */
        return call_user_func_array(
            [$this->handler, $method],
            array_values($arguments)
        );
    }

    /**
     * @return string
     */
    public function getDefinition(): string
    {
        return $this->definition;
    }

    /**
     * @return object|null
     */
    public function getHandler(): object | null
    {
        return $this->handler;
    }
}
