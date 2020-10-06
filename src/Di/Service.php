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
use function is_array;

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
    /**
     * @var array
     */
    protected array $definition;

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
     * @param array $definition
     * @param bool  $shared
     */
    final public function __construct(array $definition, bool $shared = false)
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

//        let found = true,
//            instance = null;
//
//        let definition = this->definition;
//        if typeof definition == "string" {
//            /**
//             * String definitions can be class names without implicit parameters
//             */
//            if container !== null {
//                let instance = container->get(definition, parameters);
//            } elseif class_exists(definition) {
//                if typeof parameters == "array" && count(parameters) {
//                    let instance = create_instance_params(
//                        definition,
//                        parameters
//                    );
//                } else {
//                    let instance = create_instance(definition);
//                }
//            } else {
//                let found = false;
//            }
//        } else {
//
//            /**
//             * Object definitions can be a Closure or an already resolved
//             * instance
//             */
//            if typeof definition == "object" {
//                if definition instanceof Closure {
//
//                    /**
//                     * Bounds the closure to the current DI
//                     */
//                    if typeof container == "object" {
//                        let definition = Closure::bind(definition, container);
//                    }
//
//                    if typeof parameters == "array" {
//                        let instance = call_user_func_array(
//                            definition,
//                            parameters
//                        );
//                    } else {
//                        let instance = call_user_func(definition);
//                    }
//                } else {
//                    let instance = definition;
//                }
//            } else {
//                /**
//                 * Array definitions require a 'className' parameter
//                 */
//                if typeof definition == "array" {
//                    let builder = new Builder(),
//                        instance = builder->build(
//                            container,
//                            definition,
//                            parameters
//                        );
//                } else {
//                    let found = false;
//                }
//            }
//        }
//
//        /**
//         * If the service can't be built, we must throw an exception
//         */
//        if unlikely found === false {
//            throw new ServiceResolutionException();
//        }
//
//        /**
//         * Update the shared instance if the service is shared
//         */
//        if shared {
//            let this->sharedInstance = instance;
//        }
//
//        let this->resolved = true;
//
//        return instance;
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
        $definition = $this->definition;

//        /**
//         * Update the parameter
//         */
//        if fetch arguments, definition["arguments"] {
//            let arguments[position] = parameter;
//        } else {
//            let arguments = [position: parameter];
//        }
//
//        /**
//         * Re-update the arguments
//         */
//        let definition["arguments"] = arguments;

        /**
         * Re-update the definition
         */
        $this->definition = $definition;

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
