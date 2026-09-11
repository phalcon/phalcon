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
use Phalcon\Contracts\Di\DiTypes;
use Phalcon\Di\Exception\ServiceResolutionException;
use Phalcon\Di\Exceptions\DefinitionMustBeArrayForRead;
use Phalcon\Di\Exceptions\DefinitionMustBeArrayForUpdate;
use Phalcon\Di\Service\Builder;

use function call_user_func;
use function call_user_func_array;
use function class_exists;
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
 * @phpstan-import-type di_parameters from DiTypes
 * @phpstan-import-type di_service_argument from DiTypes
 * @phpstan-import-type di_service_definition from DiTypes
 */
class Service implements ServiceInterface
{
    protected mixed $definition;

    protected bool $resolved = false;

    protected bool $shared = false;

    /**
     * @var mixed
     */
    protected $sharedInstance;

    /**
     * Service constructor.
     */
    final public function __construct(mixed $definition, bool $shared = false)
    {
        $this->definition = $definition;
        $this->shared     = $shared;
    }

    /**
     * Returns the service definition
     */
    public function getDefinition(): mixed
    {
        return $this->definition;
    }

    /**
     * Returns a parameter in a specific position
     */
    public function getParameter(int $position): mixed
    {
        if (!is_array($this->definition)) {
            throw new DefinitionMustBeArrayForRead();
        }

        /** @var di_service_definition $definition */
        $definition = $this->definition;

        return $definition['arguments'][$position] ?? null;
    }

    /**
     * Returns true if the service was resolved
     */
    public function isResolved(): bool
    {
        return $this->resolved;
    }

    /**
     * Check whether the service is shared or not
     */
    public function isShared(): bool
    {
        return $this->shared;
    }

    /**
     * Resolves the service
     *
     * @phpstan-param di_parameters|null $parameters
     *
     * @return mixed|null
     * @throws Exception
     * @throws ServiceResolutionException
     */
    public function resolve(
        array | null $parameters = null,
        DiInterface | null $container = null
    ): mixed {
        /**
         * Check if the service is shared
         */
        if (true === $this->shared && null !== $this->sharedInstance) {
            return $this->sharedInstance;
        }

        $found    = true;
        $instance = null;

        $definition = $this->definition;
        if (is_string($definition)) {
            /**
             * String definitions can be class names without implicit parameters
             */
            if (class_exists($definition)) {
                if (is_array($parameters) && !empty($parameters)) {
                    $instance = new $definition(...$parameters);
                } else {
                    $instance = new $definition();
                }
            } else {
                $found = false;
            }
        } else {
            /**
             * Object definitions can be a Closure or an already resolved
             * instance
             */
            if (is_object($definition)) {
                if ($definition instanceof Closure) {
                    /**
                     * Bounds the closure to the current DI
                     */
                    if (null !== $container) {
                        $definition = Closure::bind($definition, $container);
                    }

                    if (is_array($parameters)) {
                        $instance = call_user_func_array($definition, $parameters);
                    } else {
                        $instance = call_user_func($definition);
                    }
                } else {
                    $instance = $definition;
                }
            } else {
                /**
                 * Array definitions require a 'className' parameter
                 */
                if (is_array($definition)) {
                    $builder  = new Builder();
                    $instance = $builder->build(
                        $container,
                        $definition,
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
        if (false === $found) {
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
     */
    public function setDefinition(mixed $definition): void
    {
        $this->definition = $definition;
    }

    /**
     * Changes a parameter in the definition without resolve the service
     *
     * @phpstan-param di_service_argument $parameter
     *
     * @throws Exception
     */
    public function setParameter(int $position, array $parameter): ServiceInterface
    {
        if (!is_array($this->definition)) {
            throw new DefinitionMustBeArrayForUpdate();
        }

        /** @var di_service_definition $definition */
        $definition = $this->definition;

        /**
         * Update the parameter
         */
        if (isset($definition['arguments'])) {
            $arguments            = $definition['arguments'];
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
     */
    public function setShared(bool $shared): void
    {
        $this->shared = $shared;
    }

    /**
     * Sets/Resets the shared instance related to the service
     */
    public function setSharedInstance(mixed $sharedInstance): void
    {
        $this->sharedInstance = $sharedInstance;
    }
}
