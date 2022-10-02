<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Di;

use Phalcon\Di\Exception as DiException;
use Phalcon\Di\Exception\ServiceResolutionException;
use Phalcon\Di\Traits\DiArrayAccessTrait;
use Phalcon\Di\Traits\DiEventsTrait;
use Phalcon\Di\Traits\DiExceptionsTrait;
use Phalcon\Di\Traits\DiInstanceTrait;
use Phalcon\Di\Traits\DiLoadTrait;
use Phalcon\Events\ManagerInterface;
use Phalcon\Events\Traits\EventsAwareTrait;

/**
 * Phalcon\Di\Di is a component that implements Dependency Injection/Service
 * Location of services, and it's itself a container for them.
 *
 * Since Phalcon is highly decoupled, Phalcon\Di\Di is essential to integrate the
 * different components of the framework. The developer can also use this
 * component to inject dependencies and manage global instances of the different
 * classes used in the application.
 *
 * Basically, this component implements the `Inversion of Control` pattern.
 * Applying this, the objects do not receive their dependencies using setters or
 * constructors, but requesting a service dependency injector. This reduces the
 * overall complexity, since there is only one way to get the required
 * dependencies within a component.
 *
 * Additionally, this pattern increases testability in the code, thus making it
 * less prone to errors.
 *
 *```php
 * use Phalcon\Di\Di;
 * use Phalcon\Http\Request;
 *
 * $di = new Di();
 *
 * // Using a string definition
 * $di->set("request", Request::class, true);
 *
 * // Using an anonymous function
 * $di->setShared(
 *     "request",
 *     function () {
 *         return new Request();
 *     }
 * );
 *
 * $request = $di->getRequest();
 *```
 */
class Di implements DiInterface
{
    use DiArrayAccessTrait;
    use DiEventsTrait;
    use DiExceptionsTrait;
    use DiInstanceTrait;
    use DiLoadTrait;
    use EventsAwareTrait;

    /**
     * List of registered services
     *
     * @var ServiceInterface[]
     */
    protected array $services = [];

    /**
     * List of shared instances
     *
     * @var array
     */
    protected array $sharedInstances = [];

    /**
     * Latest DI build
     *
     * @var DiInterface|null
     */
    protected static ?DiInterface $defaultContainer = null;

    /**
     * Phalcon\Di\Di constructor
     */
    public function __construct()
    {
        if (null === self::$defaultContainer) {
            self::$defaultContainer = $this;
        }
    }

    /**
     * Magic method to get or set services using setters/getters
     *
     * @param string $method
     * @param array  $arguments
     *
     * @return mixed|void
     * @throws Exception
     */
    public function __call(string $method, array $arguments = [])
    {
        /**
         * If the magic method starts with "get" we try to get a service with
         * that name
         */
        if (true === str_starts_with($method, 'get')) {
            $possibleService = lcfirst(substr($method, 3));

            if (true === isset($this->services[$possibleService])) {
                return $this->get($possibleService, $arguments);
            }
        }

        /**
         * If the magic method starts with "set" we try to set a service using
         * that name
         */
        if (true === str_starts_with($method, 'set')) {
            $definition = $arguments[0] ?? null;
            if (null !== $definition) {
                $this->set(lcfirst(substr($method, 3)), $definition);

                return null;
            }
        }

        $this->throwUndefinedMethod($method);
    }

    /**
     * Attempts to register a service in the services container
     * Only is successful if a service hasn't been registered previously
     * with the same name
     *
     * @param string $name
     * @param mixed  $definition
     * @param bool   $shared
     *
     * @return bool|mixed|Service|ServiceInterface
     */
    public function attempt(string $name, $definition, bool $shared = false)
    {
        if (true === isset($this->services[$name])) {
            return false;
        }

        $this->services[$name] = new Service($definition, $shared);

        return $this->services[$name];
    }

    /**
     * Resolves the service based on its configuration
     *
     * @param string     $name
     * @param array|null $parameters
     *
     * @return mixed
     * @throws Exception
     */
    public function get(string $name, array $parameters = null): mixed
    {
        $instance = null;
        $service  = null;

        /**
         * If the service is shared, and it already has a cached instance then
         * immediately return it without triggering events.
         */
        if (true === isset($this->services[$name])) {
            $service = $this->services[$name];

            if (
                true === $service->isShared() &&
                true === isset($this->sharedInstances[$name])
            ) {
                return $this->sharedInstances[$name];
            }
        }

        /**
         * Allows for custom creation of instances through the
         * "di:beforeServiceResolve" event.
         */
        $instance = $this->fireBeforeServiceResolve(
            $this->eventsManager,
            $name,
            $parameters,
            $instance
        );

        if (true !== is_object($instance)) {
            $instance = $this->processObjectNotNullService(
                $name,
                $parameters,
                $service,
                $instance
            );
            $instance = $this->processObjectNullService(
                $name,
                $parameters,
                $service,
                $instance
            );
        }

        /**
         * Pass the DI to the instance if it implements
         * \Phalcon\Di\InjectionAwareInterface
         */
        if ($instance instanceof InjectionAwareInterface) {
            $instance->setDI($this);
        }

        if ($instance instanceof InitializationAwareInterface) {
            $instance->initialize();
        }

        /**
         * Allows for post creation instance configuration through the
         * "di:afterServiceResolve" event.
         */
        return $this->fireAfterServiceResolve(
            $this->eventsManager,
            $name,
            $parameters,
            $instance
        );
    }

    /**
     * Return the latest DI created
     *
     * @return DiInterface|null
     */
    public static function getDefault(): ?DiInterface
    {
        if (null === self::$defaultContainer) {
            self::$defaultContainer = new Di();
        }

        return self::$defaultContainer;
    }

    /**
     * Returns the internal event manager
     *
     * @return ManagerInterface|null
     */
    public function getInternalEventsManager(): ?ManagerInterface
    {
        return $this->eventsManager;
    }

    /**
     * Returns a service definition without resolving
     *
     * @param string $name
     *
     * @return mixed
     * @throws Exception
     */
    public function getRaw(string $name): mixed
    {
        return $this->getService($name)
                    ->getDefinition()
        ;
    }

    /**
     * Returns a Phalcon\Di\Service instance
     *
     * @param string $name
     *
     * @return ServiceInterface
     * @throws Exception
     */
    public function getService(string $name): ServiceInterface
    {
        if (true !== $this->has($name)) {
            $this->throwServiceNotFound($name);
        }

        return $this->services[$name];
    }

    /**
     * Return the services registered in the DI
     *
     * @return array
     */
    public function getServices(): array
    {
        return $this->services;
    }

    /**
     * Resolves a service, the resolved service is stored in the DI, subsequent
     * requests for this service will return the same instance
     *
     * @param string     $name
     * @param array|null $parameters
     *
     * @return mixed
     * @throws DiException
     */
    public function getShared(string $name, array $parameters = null): mixed
    {
        if (true !== isset($this->sharedInstances[$name])) {
            // Store the instance in the shared instances cache.
            $this->sharedInstances[$name] = $this->get($name, $parameters);
        }

        return $this->sharedInstances[$name];
    }

    /**
     * Check whether the DI contains a service by a name
     *
     * @param string $name
     *
     * @return bool
     */
    public function has(string $name): bool
    {
        return isset($this->services[$name]);
    }

    /**
     * Registers a service provider.
     *
     * ```php
     * use Phalcon\Di\DiInterface;
     * use Phalcon\Di\ServiceProviderInterface;
     *
     * class SomeServiceProvider implements ServiceProviderInterface
     * {
     *     public function register(DiInterface $di)
     *     {
     *         $di->setShared(
     *             'service',
     *             function () {
     *                 // ...
     *             }
     *         );
     *     }
     * }
     * ```
     *
     * @param ServiceProviderInterface $provider
     *
     * @return void
     */
    public function register(ServiceProviderInterface $provider): void
    {
        $provider->register($this);
    }

    /**
     * Removes a service in the services container
     * It also removes any shared instance created for the service
     *
     * @param string $name
     *
     * @return void
     */
    public function remove(string $name): void
    {
        unset($this->services[$name]);
        unset($this->sharedInstances[$name]);
    }

    /**
     * Resets the internal default DI
     *
     * @return void
     */
    public static function reset(): void
    {
        self::$defaultContainer = null;
    }

    /**
     * Registers a service in the services container
     *
     * @param string $name
     * @param mixed  $definition
     * @param bool   $shared
     *
     * @return ServiceInterface
     */
    public function set(
        string $name,
        mixed $definition,
        bool $shared = false
    ): ServiceInterface {
        $this->services[$name] = new Service($definition, $shared);

        return $this->services[$name];
    }

    /**
     * Set a default dependency injection container to be obtained into static
     * methods
     *
     * @param DiInterface $container
     *
     * @return void
     */
    public static function setDefault(DiInterface $container): void
    {
        self::$defaultContainer = $container;
    }

    /**
     * Sets the internal event manager
     *
     * @param ManagerInterface $eventsManager
     */
    public function setInternalEventsManager(ManagerInterface $eventsManager)
    {
        $this->eventsManager = $eventsManager;
    }

    /**
     * Sets a service using a raw Phalcon\Di\Service definition
     *
     * @param string           $name
     * @param ServiceInterface $rawDefinition
     *
     * @return ServiceInterface
     */
    public function setService(string $name, ServiceInterface $rawDefinition): ServiceInterface
    {
        $this->services[$name] = $rawDefinition;

        return $rawDefinition;
    }

    /**
     * @param string $name
     * @param mixed  $definition
     *
     * @return ServiceInterface
     */
    public function setShared(string $name, mixed $definition): ServiceInterface
    {
        return $this->set($name, $definition, true);
    }

    /**
     * @param string                $name
     * @param array|null            $parameters
     * @param ServiceInterface|null $service
     * @param mixed                 $instance
     *
     * @return mixed|null
     * @throws Exception
     */
    private function processObjectNotNullService(
        string $name,
        array $parameters = null,
        ServiceInterface $service = null,
        mixed $instance = null
    ) {
        if (null !== $service) {
            // The service is registered in the DI.
            try {
                $instance = $service->resolve($parameters, $this);
            } catch (ServiceResolutionException $ex) {
                $this->throwCannotResolveService($name);
            }

            // If the service is shared then we'll cache the instance.
            if (true === $service->isShared()) {
                $this->sharedInstances[$name] = $instance;
            }
        }

        return $instance;
    }

    /**
     * @param string                $name
     * @param array|null            $parameters
     * @param ServiceInterface|null $service
     * @param mixed                 $instance
     *
     * @return mixed|null
     * @throws Exception
     */
    private function processObjectNullService(
        string $name,
        array $parameters = null,
        ServiceInterface $service = null,
        mixed $instance = null
    ): mixed {
        if (null === $service) {
            /**
             * The DI also acts as builder for any class even if it isn't
             * defined in the DI
             */
            $this->checkClassExists($name);

            $instance = $this->createInstance($name, $parameters);
        }

        return $instance;
    }
}
