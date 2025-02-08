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

namespace Phalcon\Mvc\Router;

use Phalcon\Components\Attributes\Adapter\Memory;
use Phalcon\Components\Attributes\Parser\Attribute;
use Phalcon\Components\Attributes\Parser\Exception;
use Phalcon\Events\Exception as EventsException;
use Phalcon\Mvc\Router;
use Phalcon\Traits\Helper\Str\UncamelizeTrait;

use function array_pop;
use function call_user_func;
use function explode;
use function implode;
use function is_array;
use function is_callable;
use function is_object;
use function is_string;
use function preg_match;
use function str_replace;
use function strtolower;
use function strtoupper;

/**
 * A router that reads routes attributes from classes/resources
 *
 * ```php
 * use Phalcon\Mvc\Router\Attributes;
 *
 * $di->setShared(
 *     "router",
 *     function() {
 *         // Use the attributes router
 *         $router = new Attributes(false);
 *
 *         // This will do the same as above but only if the handled uri
 *         // starts with /invoices
 *         $router->addResource("Invoices", "/invoices");
 *
 *         return $router;
 *     }
 * );
 * ```
 */
class Attributes extends Router
{
    use UncamelizeTrait;

    /**
     * @mixed callable|string|null
     */
    protected mixed $actionPreformatCallback = null;

    /**
     * @mixed string
     */
    protected string $actionSuffix = "Action";

    /**
     * @mixed string
     */
    protected string $controllerSuffix = "Controller";

    /**
     * @mixed array
     */
    protected array $handlers = [];

    /**
     * @mixed string
     */
    protected string $routePrefix = "";

    /**
     * Adds a resource to the attributes handler
     * A resource is a class that contains routing attributes
     * The class is located in a module
     *
     * @param string      $module
     * @param string      $handler
     * @param string|null $prefix
     *
     * @return self
     */
    public function addModuleResource(
        string $module,
        string $handler,
        string $prefix = null
    ): Attributes {
        $this->handlers[] = [$prefix, $handler, $module];

        return $this;
    }

    /**
     * Adds a resource to the attributes handler
     * A resource is a class that contains routing attributes
     *
     * @param string      $handler
     * @param string|null $prefix
     *
     * @return $this
     */
    public function addResource(
        string $handler,
        string $prefix = null
    ): Attributes {
        $this->handlers[] = [$prefix, $handler];

        return $this;
    }

    /**
     * @return callable|string|null
     */
    public function getActionPreformatCallback(): callable | string | null
    {
        return $this->actionPreformatCallback;
    }

    /**
     * Return the registered resources
     *
     * @return array
     */
    public function getResources(): array
    {
        return $this->handlers;
    }

    /**
     * Produce the routing parameters from the rewrite information
     *
     * @param string $uri
     *
     * @throws EventsException
     * @throws Exception|\Phalcon\Mvc\Router\Exception
     * @return void
     */
    public function handle(string $uri): void
    {
        if (null === $this->container) {
            throw new Exception(
                "A dependency injection container is required to "
                . "access the 'attributes' service"
            );
        }

        /** @var Memory $attributesService */
        $attributesService = $this->container->getShared("attributes");

        foreach ($this->handlers as $scope) {
            if (!is_array($scope)) {
                continue;
            }

            /**
             * A prefix (if any) must be in position 0
             */
            $prefix = $scope[0];

            if (!empty($prefix)) {
                /**
                 * Route object is used to compile patterns
                 */
                $route = new Route($prefix);

                /**
                 * Compiled patterns can be valid regular expressions.
                 * In that case We only need to check if it starts with
                 * the pattern, so we remove to "$" from the end.
                 */
                $compiledPattern = str_replace(
                    "$#",
                    "#",
                    $route->getCompiledPattern()
                );

                if (str_contains($compiledPattern, "^")) {
                    /**
                     * If it's a regular expression, it will contain the "^"
                     */
                    if (!preg_match($compiledPattern, $uri)) {
                        continue;
                    }
                } elseif (!str_starts_with($uri, $prefix)) {
                    continue;
                }
            }

            /**
             * The controller must be in position 1
             */
            $handler = $scope[1];

            if (str_contains($handler, "\\")) {
                /**
                 * Extract the real class name from the namespaced class
                 * The lowercased class name is used as controller
                 * Extract the namespace from the namespaced class
                 */
                $controllerNameArray = explode("\\", $handler);

                // Extract the real class name from the namespaced class
                $controllerName = array_pop($controllerNameArray);

                // Extract the namespace from the namespaced class
                $namespaceName = implode("\\", $controllerNameArray);
            } else {
                $controllerName = $handler;

                $namespaceName = $this->defaultNamespace;
            }

            $this->routePrefix = '';

            /**
             * Check if the scope has a module associated
             */
            $moduleName = $scope[2] ?? null;
            $moduleName = $moduleName !== null ? $moduleName : "";
            $suffixed   = $controllerName . $this->controllerSuffix;

            /**
             * Add namespace to class if one is set
             */
            if (null !== $namespaceName) {
                $suffixed = $namespaceName . "\\" . $suffixed;
            }

            /**
             * Get the attributes from the class
             */
            $handlerAttributes = $attributesService->get($suffixed);

            if (!is_object($handlerAttributes)) {
                continue;
            }

            /**
             * Process class attributes
             */
            $classAttributes = $handlerAttributes->getClassAttributes();

            if (is_object($classAttributes)) {
                $attributes = $classAttributes->getAttributes();

                foreach ($attributes as $attribute) {
                    $this->processControllerAttribute($attribute);
                }
            }

            /**
             * Process method attributes
             */
            $methodAttributes = $handlerAttributes->getMethodsAttributes();

            $lowerControllerName = $this->toUncamelize($controllerName);

            foreach ($methodAttributes as $method => $collection) {
                if (!is_object($collection)) {
                    continue;
                }

                foreach ($collection->getAttributes() as $attribute) {
                    $this->processActionAttribute(
                        $moduleName,
                        $namespaceName,
                        $lowerControllerName,
                        $method,
                        $attribute
                    );
                }
            }
        }

        /**
         * Call the parent handle method()
         */
        parent::handle($uri);
    }

    /**
     * Checks for attributes in the public methods of the controller
     *
     * @param string    $module
     * @param string    $namespaceName
     * @param string    $controller
     * @param string    $action
     * @param Attribute $attribute
     *
     * @throws Exception|\Phalcon\Mvc\Router\Exception
     * @return void
     */
    public function processActionAttribute(
        string $module,
        string $namespaceName,
        string $controller,
        string $action,
        Attribute $attribute
    ): void {
        $name    = $attribute->getName();
        $isRoute = match ($name) {
            "Route",
            "Get",
            "Post",
            "Put",
            "Patch",
            "Delete",
            "Options" => true,
            default => false,
        };

        $methods = match ($name) {
            "Get",
            "Post",
            "Put",
            "Patch",
            "Delete",
            "Options" => strtoupper($name),
            default => null,
        };

        if (true !== $isRoute) {
            return;
        }

        $proxyActionName = str_replace($this->actionSuffix, "", $action);

        if (null !== $this->actionPreformatCallback) {
            $proxyActionName = call_user_func(
                $this->actionPreformatCallback,
                $proxyActionName
            );
        }

        $arguments = $attribute->getArguments();

        $actionName = strtolower($proxyActionName);

        /**
         * Check for existing paths in the attribute
         */
        $paths = $arguments["paths"] ?? [];

        if (!is_array($paths)) {
            $paths = [];
        }

        /**
         * Update the module if any
         */
        if (!empty($module)) {
            $paths["module"] = $module;
        }

        /**
         * Update the namespace if any
         */
        if (!empty($namespaceName)) {
            $paths["namespace"] = $namespaceName;
        }

        $paths["controller"] = $controller;
        $paths["action"]     = $actionName;

        $value = $attribute->hasArgument(0) ?
            $attribute->getArgument(0) :
            $attribute->getArgument('route');

        /**
         * Create the route using the prefix
         */
        if ($value !== null) {
            if ($value != "/") {
                $uri = $this->routePrefix . $value;
            } else {
                if (true !== empty($this->routePrefix)) {
                    $uri = $this->routePrefix;
                } else {
                    $uri = $value;
                }
            }
        } else {
            $uri = $this->routePrefix . $actionName;
        }

        /**
         * Add the route to the router
         */
        $route = $this->add($uri, $paths);

        /**
         * Add HTTP constraint methods
         */
        if ($methods === null) {
            $methods = $arguments["methods"] ?? null;
        }

        if (is_array($methods) || is_string($methods)) {
            $route->via($methods);
        }

        /**
         * Add the converters
         */
        $converts = $arguments["converts"] ?? null;
        if (is_array($converts)) {
            foreach ($converts as $param => $convert) {
                $route->convert($param, $convert);
            }
        }

        /**
         * Add the converters
         */
        $converts = $arguments["converters"] ?? null;
        if (is_array($converts)) {
            foreach ($converts as $param => $convert) {
                $route->convert($param, $convert);
            }
        }

        /**
         * Add the converters
         */
        $beforeMatch = $arguments["beforeMatch"] ?? null;

        if (is_array($beforeMatch) || is_string($beforeMatch)) {
            $route->beforeMatch($beforeMatch);
        }

        $routeName = $arguments["name"] ?? null;

        if (is_string($routeName)) {
            $route->setName($routeName);
        }
    }

    /**
     * Checks for attributes in the controller docblock
     *
     * @param Attribute $attribute
     *
     * @return void
     */
    public function processControllerAttribute(Attribute $attribute): void
    {
        /**
         * @RoutePrefix add a prefix for all the routes defined in the model
         */
        if ($attribute->getName() === 'RoutePrefix') {
            $this->routePrefix = $attribute->hasArgument(0) ?
                $attribute->getArgument(0) :
                $attribute->getArgument('prefix') ?? '';
        }
    }

    /**
     * Sets the action preformat callback
     * $action here already without suffix 'Action'
     *
     * ```php
     * // Array as callback
     * $attributeRouter->setActionPreformatCallback(
     *      [
     *          new Uncamelize(),
     *          '__invoke'
     *      ]
     *  );
     *
     * // Function as callback
     * $attributeRouter->setActionPreformatCallback(
     *     function ($action) {
     *         return $action;
     *     }
     * );
     *
     * // String as callback
     * $attributeRouter->setActionPreformatCallback('strtolower');
     *
     * // If empty method constructor called [null], sets uncamelize with - delimiter
     * $attributeRouter->setActionPreformatCallback();
     * ```
     *
     * @param callable|string|null $callback
     *
     * @throws Exception
     * @return Attributes
     */
    public function setActionPreformatCallback(callable | string | null $callback = null): Attributes
    {
        if (is_callable($callback)) {
            $this->actionPreformatCallback = $callback;
        } elseif ($callback === null) {
            $this->actionPreformatCallback = function ($action) {
                return $this->toUncamelize($action, "-");
            };
        } else {
            throw new Exception(
                "The 'callback' parameter must be either a callable or NULL."
            );
        }

        return $this;
    }

    /**
     * Changes the action method suffix
     *
     * @param string $actionSuffix
     *
     * @return Attributes
     */
    public function setActionSuffix(string $actionSuffix): Attributes
    {
        $this->actionSuffix = $actionSuffix;

        return $this;
    }

    /**
     * Changes the controller class suffix
     *
     * @param string $controllerSuffix
     *
     * @return Attributes
     */
    public function setControllerSuffix(string $controllerSuffix): Attributes
    {
        $this->controllerSuffix = $controllerSuffix;

        return $this;
    }
}
