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

use Phalcon\Di\Exception\ServiceResolutionException;
use Phalcon\Config\Adapter\Php;
use Phalcon\Config\Adapter\Yaml;
use Phalcon\Config\ConfigInterface;
use Phalcon\Events\ManagerInterface;
use Phalcon\Events\Traits\EventsAwareTrait;
use function is_object;

/**
 * Phalcon\Di is a component that implements Dependency Injection/Service
 * Location of services and it's itself a container for them.
 *
 * Since Phalcon is highly decoupled, Phalcon\Di is essential to integrate the
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
 * use Phalcon\Di;
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
 *
 * @property array            $services
 * @property array            $sharedInstances
 * @property DiInterface|null $_default

*/
class Di implements DiInterface
{
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
    protected static $_default;

    /**
     * Di constructor.
     */
    public function __construct()
    {
        if (!self::$_default) {
            self::$_default = $this;
        }
    }

    /**
     * Magic method to get or set services using setters/getters
     */
    /**
     * @param string $method
     * @param array  $arguments
     *
     * @return mixed|null
     * @throws Exception
     */
    public function __call(string $method, array $arguments = [])
    {
//        /**
//         * If the magic method starts with "get" we try to get a service with
//         * that name
//         */
//        if starts_with(method, "get") {
//            let possibleService = lcfirst(substr(method, 3));
//
//            if isset this->services[possibleService] {
//                let instance = this->get(possibleService, arguments);
//
//                return instance;
//            }
//        }
//
//        /**
//         * If the magic method starts with "set" we try to set a service using
//         * that name
//         */
//        if starts_with(method, "set") {
//            if fetch definition, arguments[0] {
//                this->set(
//                    lcfirst(
//                        substr(method, 3)
//                    ),
//                    definition
//                );
//
//                return null;
//            }
//        }

        /**
         * The method doesn't start with set/get throw an exception
         */
        throw new Exception(
            "Call to undefined method or service '" . $method . "'"
        );
    }

    /**
     * Attempts to register a service in the services container
     * Only is successful if a service hasn't been registered previously
     * with the same name
     */
    /**
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
    public function get(string $name, array $parameters = null)
    {
        $instance = null;
        $isShared = false;

        /**
         * If the service is shared and it already has a cached instance then
         * immediately return it without triggering events.
         */
        if (true === isset($this->services[$name])) {
            $service  = $this->services[$name];
            $isShared = $service->isShared();

            if (
                true === $isShared &&
                true === isset($this->sharedInstances[$name])
            ) {
                return $this->sharedInstances[$name];
            }
        }

        /**
         * Allows for custom creation of instances through the
         * "di:beforeServiceResolve" event.
         */
        if (true === is_object($this->eventsManager)) {
            $instance = $this->eventsManager->fire(
                "di:beforeServiceResolve",
                $this,
                [
                    'name'       => $name,
                    'parameters' => $parameters,
                ]
            );
        }

        if (true !== is_object($instance)) {
            if (null !== $service) {
                // The service is registered in the DI.
                try {
                    $instance = $service->resolve($parameters, $this);
                } catch (ServiceResolutionException $ex) {
                    throw new Exception(
                        "Service '" . $name . "' cannot be resolved"
                    );
                }

                // If the service is shared then we'll cache the instance.
                if (true === $isShared) {
                    $this->sharedInstances[$name] = $instance;
                }
            } else {
                /**
                 * The DI also acts as builder for any class even if it isn't
                 * defined in the DI
                 */
                if (true !== class_exists($name)) {
                    throw new Exception(
                        "Service '" . $name . "' wasn't found in the dependency injection container"
                    );
                }

                if (true === is_array($parameters) && true !== empty($parameters)) {
                    $instance = new $name(...$parameters);
                } else {
                    $instance = new $name();
                }
            }
        }

        /**
         * Pass the DI to the instance if it implements
         * \Phalcon\Di\InjectionAwareInterface
         */
        if (
            true === is_object($instance) &&
            $instance instanceof InjectionAwareInterface
        ) {
            $instance->setDI($this);
        }

        /**
         * Allows for post creation instance configuration through the
         * "di:afterServiceResolve" event.
         */
        if (true === is_object($this->eventsManager)) {
            $instance = $this->eventsManager->fire(
                "di:afterServiceResolve",
                $this,
                [
                    'name'       => $name,
                    'parameters' => $parameters,
                    'instance'   => $instance,
                ]
            );
        }

        return $instance;
    }

    /**
     * Return the latest DI created
     *
     * @return DiInterface|null
     */
    public static function getDefault(): ?DiInterface
    {
        return self::$_default;
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
    public function getRaw(string $name)
    {
        $this->checkService($name);

        $service = $this->services[$name];

        return $service->getDefinition();
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
        $this->checkService($name);

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
     * @return mixed|InjectionAwareInterface|null
     * @throws Exception
     */
    public function getShared(string $name, array $parameters = null)
    {
        $instance = $this->sharedInstances[$name] ?? null;
        if (null === $instance) {
            $instance = $this->get($name, $parameters);

            $this->sharedInstances[$name] = $instance;
        }

        return $instance;
    }

//    /**
//     * Loads services from a Config object.
//     */
//    protected function loadFromConfig(<ConfigInterface> config): void
//    {
//        var services, name, service;
//
//        let services = config->toArray();
//
//        for name, service in services {
//            this->set(
//                name,
//                service,
//                isset service["shared"] && service["shared"]
//            );
//        }
//    }
//
//    /**
//     * Loads services from a php config file.
//     *
//     * ```php
//     * $di->loadFromPhp("path/services.php");
//     * ```
//     *
//     * And the services can be specified in the file as:
//     *
//     * ```php
//     * return [
//     *      'myComponent' => [
//     *          'className' => '\Acme\Components\MyComponent',
//     *          'shared' => true,
//     *      ],
//     *      'group' => [
//     *          'className' => '\Acme\Group',
//     *          'arguments' => [
//     *              [
//     *                  'type' => 'service',
//     *                  'service' => 'myComponent',
//     *              ],
//     *          ],
//     *      ],
//     *      'user' => [
//     *          'className' => '\Acme\User',
//     *      ],
//     * ];
//     * ```
//     *
//     * @link https://docs.phalcon.io/en/latest/reference/di.html
//     */
//    public function loadFromPhp(string! filePath): void
//    {
//        var services;
//
//        let services = new Php(filePath);
//
//        this->loadFromConfig(services);
//    }
//
//    /**
//     * Loads services from a yaml file.
//     *
//     * ```php
//     * $di->loadFromYaml(
//     *     "path/services.yaml",
//     *     [
//     *         "!approot" => function ($value) {
//     *             return dirname(__DIR__) . $value;
//     *         }
//     *     ]
//     * );
//     * ```
//     *
//     * And the services can be specified in the file as:
//     *
//     * ```php
//     * myComponent:
//     *     className: \Acme\Components\MyComponent
//     *     shared: true
//     *
//     * group:
//     *     className: \Acme\Group
//     *     arguments:
//     *         - type: service
//     *           name: myComponent
//     *
//     * user:
//     *    className: \Acme\User
//     * ```
//     *
//     * @link https://docs.phalcon.io/en/latest/reference/di.html
//     */
//    public function loadFromYaml(string! filePath, array! callbacks = null): void
//    {
//        var services;
//
//        let services = new Yaml(filePath, callbacks);
//
//        this->loadFromConfig(services);
//    }

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
     * Allows to obtain a shared service using the array syntax
     *
     *```php
     * var_dump($di["request"]);
     *```
     *
     * @param mixed $name
     *
     * @return mixed|InjectionAwareInterface|null
     * @throws Exception
     */
    public function offsetGet($name)
    {
        return $this->getShared($name);
    }

    /**
     * Check if a service is registered using the array syntax
     *
     * @param mixed $name
     *
     * @return bool
     */
    public function offsetExists($name): bool
    {
        return $this->has($name);
    }

    /**
     * Allows to register a shared service using the array syntax
     *
     *```php
     * $di["request"] = new \Phalcon\Http\Request();
     *```
     *
     * @param mixed $name
     * @param mixed $definition
     */
    public function offsetSet($name, $definition): void
    {
        $this->setShared($name, $definition);
    }

    /**
     * Removes a service from the services container using the array syntax
     *
     * @param mixed $name
     */
    public function offsetUnset($name): void
    {
        $this->remove($name);
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
     */
    public function remove(string $name): void
    {
        unset($this->services[$name]);
        unset($this->sharedInstances[$name]);
    }

    /**
     * Resets the internal default DI
     */
    public static function reset(): void
    {
        self::$_default = null;
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
    public function set(string $name, $definition, bool $shared = false): ServiceInterface
    {
        $this->services[$name] = new Service($definition, $shared);

        return $this->services[$name];
    }

    /**
     * Set a default dependency injection container to be obtained into static
     * methods
     *
     * @param DiInterface $container
     */
    public static function setDefault(DiInterface $container): void
    {
        self::$_default = $container;
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
     * Registers an "always shared" service in the services container
     *
     * @param string $name
     * @param mixed  $definition
     *
     * @return ServiceInterface
     */
    public function setShared(string $name, $definition): ServiceInterface
    {
        return $this->set($name, $definition, true);
    }

    /**
     * @param string $name
     *
     * @throws Exception
     */
    private function checkService(string $name): void
    {
        if (true !== $this->has($name)) {
            throw new Exception(
                "Service '" . $name . "' was not found in the dependency injection container"
            );
        }
    }
}
