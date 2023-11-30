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

use Phalcon\Annotations\Adapter\Memory;
use Phalcon\Annotations\Annotation;
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
 * A router that reads routes annotations from classes/resources
 *
 * ```php
 * use Phalcon\Mvc\Router\Annotations;
 *
 * $di->setShared(
 *     "router",
 *     function() {
 *         // Use the annotations router
 *         $router = new Annotations(false);
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
class Annotations extends Router
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
     * Adds a resource to the annotations handler
     * A resource is a class that contains routing annotations
     * The class is located in a module
     *
     * @param string      $module
     * @param string      $handler
     * @param string|null $prefix
     *
     * @return Annotations
     */
    public function addModuleResource(
        string $module,
        string $handler,
        string $prefix = null
    ): Annotations {
        $this->handlers[] = [$prefix, $handler, $module];

        return $this;
    }

    /**
     * Adds a resource to the annotations handler
     * A resource is a class that contains routing annotations
     *
     * @param string      $handler
     * @param string|null $prefix
     *
     * @return $this
     */
    public function addResource(
        string $handler,
        string $prefix = null
    ): Annotations {
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
     * @return void
     * @throws EventsException
     * @throws Exception
     */
    public function handle(string $uri): void
    {
        if (null === $this->container) {
            throw new Exception(
                "A dependency injection container is required to "
                . "access the 'annotations' service"
            );
        }

        /** @var Memory $annotationsService */
        $annotationsService = $this->container->getShared("annotations");

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
            $sufixed    = $controllerName . $this->controllerSuffix;

            /**
             * Add namespace to class if one is set
             */
            if (null !== $namespaceName) {
                $sufixed = $namespaceName . "\\" . $sufixed;
            }

            /**
             * Get the annotations from the class
             */
            $handlerAnnotations = $annotationsService->get($sufixed);

            if (!is_object($handlerAnnotations)) {
                continue;
            }

            /**
             * Process class annotations
             */
            $classAnnotations = $handlerAnnotations->getClassAnnotations();

            if (is_object($classAnnotations)) {
                $annotations = $classAnnotations->getAnnotations();

                if (is_array($annotations)) {
                    foreach ($annotations as $annotation) {
                        $this->processControllerAnnotation(
                            $controllerName,
                            $annotation
                        );
                    }
                }
            }

            /**
             * Process method annotations
             */
            $methodAnnotations = $handlerAnnotations->getMethodsAnnotations();

            if (is_array($methodAnnotations)) {
                $lowerControllerName = $this->toUncamelize($controllerName);

                foreach ($methodAnnotations as $method => $collection) {
                    if (!is_object($collection)) {
                        continue;
                    }

                    foreach ($collection->getAnnotations() as $annotation) {
                        $this->processActionAnnotation(
                            $moduleName,
                            $namespaceName,
                            $lowerControllerName,
                            $method,
                            $annotation
                        );
                    }
                }
            }
        }

        /**
         * Call the parent handle method()
         */
        parent::handle($uri);
    }

    /**
     * Checks for annotations in the public methods of the controller
     *
     * @param string     $module
     * @param string     $namespaceName
     * @param string     $controller
     * @param string     $action
     * @param Annotation $annotation
     *
     * @return void
     */
    public function processActionAnnotation(
        string $module,
        string $namespaceName,
        string $controller,
        string $action,
        Annotation $annotation
    ): void {
        $name    = $annotation->getName();
        $isRoute = match ($name) {
            "Route",
            "Get",
            "Post",
            "Put",
            "Patch",
            "Delete",
            "Options" => true,
            default   => false,
        };

        $methods = match ($name) {
            "Get",
            "Post",
            "Put",
            "Patch",
            "Delete",
            "Options" => strtoupper($name),
            default   => null,
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

        $actionName = strtolower($proxyActionName);

        /**
         * Check for existing paths in the annotation
         */
        $paths = $annotation->getNamedArgument("paths");

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

        $value = $annotation->getArgument(0);

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
            $methods = $annotation->getNamedArgument("methods");
        }

        if (is_array($methods) || is_string($methods)) {
            $route->via($methods);
        }

        /**
         * Add the converters
         */
        $converts = $annotation->getNamedArgument("converts");
        if (is_array($converts)) {
            foreach ($converts as $param => $convert) {
                $route->convert($param, $convert);
            }
        }

        /**
         * Add the converters
         */
        $converts = $annotation->getNamedArgument("converters");
        if (is_array($converts)) {
            foreach ($converts as $param => $convert) {
                $route->convert($param, $convert);
            }
        }

        /**
         * Add the converters
         */
        $beforeMatch = $annotation->getNamedArgument("beforeMatch");

        if (is_array($beforeMatch) || is_string($beforeMatch)) {
            $route->beforeMatch($beforeMatch);
        }

        $routeName = $annotation->getNamedArgument("name");

        if (is_string($routeName)) {
            $route->setName($routeName);
        }
    }

    /**
     * Checks for annotations in the controller docblock
     *
     * @param string     $handler
     * @param Annotation $annotation
     *
     * @return void
     */
    public function processControllerAnnotation(
        string $handler,
        Annotation $annotation
    ): void {
        /**
         * @RoutePrefix add a prefix for all the routes defined in the model
         */
        if ($annotation->getName() == "RoutePrefix") {
            $this->routePrefix = $annotation->getArgument(0);
        }
    }

    /**
     * Sets the action preformat callback
     * $action here already without suffix 'Action'
     *
     * ```php
     * // Array as callback
     * $annotationRouter->setActionPreformatCallback(
     *      [
     *          new Uncamelize(),
     *          '__invoke'
     *      ]
     *  );
     *
     * // Function as callback
     * $annotationRouter->setActionPreformatCallback(
     *     function ($action) {
     *         return $action;
     *     }
     * );
     *
     * // String as callback
     * $annotationRouter->setActionPreformatCallback('strtolower');
     *
     * // If empty method constructor called [null], sets uncamelize with - delimiter
     * $annotationRouter->setActionPreformatCallback();
     * ```
     *
     * @param callable|string|null $callback
     *
     * @return Annotations
     * @throws Exception
     */
    public function setActionPreformatCallback(callable | string | null $callback = null): Annotations
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
     * @return Annotations
     */
    public function setActionSuffix(string $actionSuffix): Annotations
    {
        $this->actionSuffix = $actionSuffix;

        return $this;
    }

    /**
     * Changes the controller class suffix
     *
     * @param string $controllerSuffix
     *
     * @return Annotations
     */
    public function setControllerSuffix(string $controllerSuffix): Annotations
    {
        $this->controllerSuffix = $controllerSuffix;

        return $this;
    }
}
