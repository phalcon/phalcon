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

namespace Phalcon\Di;

use Closure;
use Phalcon\Di\Exception\ServiceResolutionException;
use Phalcon\Di\Service\Builder;
use Phalcon\Di\Traits\DiInstanceTrait;

use function is_array;
use function is_object;
use function is_string;

/**
 * Represents individually a service in the services container
 *
 *```php
 * $service = new \Phalcon\Di\Service(
 *     "request",
 *     \Phalcon\Http\Request::class
 * );
 *
 * $request = service->resolve();
 *```
 *
 * @property array $definition
 * @property bool  $resolved
 * @property bool  $shared
 * @property mixed $sharedInstance
 */
class Service implements ServiceInterface
{
    use DiInstanceTrait;

    /**
     * @var mixed
     */
    protected $definition;

    /**
     * @var bool
     */
    protected bool $resolved = false;

    /**
     * @var bool
     */
    protected bool $shared = false;

    /**
     * @var mixed
     */
    protected $sharedInstance;

    /**
     * Service constructor.
     *
     * @param mixed $definition
     * @param bool  $shared
     */
    final public function __construct($definition, bool $shared = false)
    {
        $this->definition = $definition;
        $this->shared     = $shared;
    }

    /**
     * Returns the service definition
     *
     * @return mixed
     */
    public function getDefinition()
    {
        return $this->definition;
    }

    /**
     * Returns a parameter in a specific position
     *
     * @return mixed
     */
    public function getParameter(int $position)
    {
        $arguments = $this->definition['arguments'] ?? [];

        return $arguments[$position] ?? null;
    }

    /**
     * Returns true if the service was resolved
     *
     * @return bool
     */
    public function isResolved(): bool
    {
        return $this->resolved;
    }

    /**
     * Check whether the service is shared or not
     *
     * @return bool
     */
    public function isShared(): bool
    {
        return $this->shared;
    }

    /**
     * Resolves the service
     *
     * @param array|null       $parameters
     * @param DiInterface|null $container
     *
     * @return mixed
     * @throws ServiceResolutionException
     */
    public function resolve(
        array $parameters = null,
        DiInterface $container = null
    ) {
        /**
         * Check if the service is shared
         */
        if (true === $this->shared && null !== $this->sharedInstance) {
            return $this->sharedInstance;
        }

        $found              = true;
        $instance           = null;
        $instanceDefinition = $this->definition;

        if (true === is_string($instanceDefinition)) {
            /**
             * String definitions can be class names without implicit parameters
             */
            if (null !== $container) {
                $instance = $container->get($instanceDefinition, $parameters);
            } elseif (true === class_exists($instanceDefinition)) {
                $instance = $this->createInstance($instanceDefinition, $parameters);
            } else {
                $found = false;
            }
        } else {

            /**
             * Object definitions can be a Closure or an already resolved
             * instance
             */
            if (true === is_object($instanceDefinition)) {
                if ($instanceDefinition instanceof Closure) {

                    /**
                     * Bounds the closure to the current DI
                     */
                    if (true === is_object($container)) {
                        $instanceDefinition = Closure::bind($instanceDefinition, $container);
                    }

                    $instance = $this->createClosureInstance(
                        $instanceDefinition,
                        $parameters
                    );
                } else {
                    $instance = $instanceDefinition;
                }
            } else {
                /**
                 * Array definitions require a 'className' parameter
                 */
                if (true === is_array($instanceDefinition)) {
                    $builder  = new Builder();
                    $instance = $builder->build(
                        $container,
                        $instanceDefinition,
                        $parameters
                    );
                } else {
                    $found = false;
                }
            }
        }

        /**
         * If the service can't be built, we must throw an exception
         */
        if (true !== $found) {
            throw new ServiceResolutionException();
        }

        /**
         * Update the shared instance if the service is shared
         */
        if (true === $this->shared) {
            $this->sharedInstance = $instance;
        }

        $this->resolved = true;

        return $instance;
    }

    /**
     * Set the service definition
     *
     * @param mixed $definition
     */
    public function setDefinition($definition): void
    {
        $this->definition = $definition;
    }

    /**
     * Changes a parameter in the definition without resolve the service
     *
     * @param int   $position
     * @param array $parameter
     *
     * @return ServiceInterface
     * @throws Exception
     */
    public function setParameter(int $position, array $parameter): ServiceInterface
    {
        if (true !== is_array($this->definition)) {
            throw new Exception(
                'Definition must be an array to update its parameters'
            );
        }

        /**
         * Update the parameter
         */
        if (true === isset($this->definition['arguments'])) {
            $arguments            = $this->definition['arguments'];
            $arguments[$position] = $parameter;
        } else {
            $arguments = [$position => $parameter];
        }

        /**
         * Re-update the arguments
         */
        $this->definition['arguments'] = $arguments;

        return $this;
    }

    /**
     * Sets if the service is shared or not
     *
     * @param bool $shared
     */
    public function setShared(bool $shared): void
    {
        $this->shared = $shared;
    }

    /**
     * Sets/Resets the shared instance related to the service
     *
     * @param $sharedInstance
     */
    public function setSharedInstance($sharedInstance): void
    {
        $this->sharedInstance = $sharedInstance;
    }
}
