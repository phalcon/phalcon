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

use Phalcon\Annotations\Annotation;
use Phalcon\Events\Exception as EventsException;
use Phalcon\Mvc\Router;
use Phalcon\Mvc\Router\Exception as RouterException;
use Phalcon\Traits\Helper\Str\CamelizeTrait;
use Phalcon\Traits\Helper\Str\UncamelizeTrait;

use function array_pop;
use function explode;
use function implode;
use function is_array;
use function is_object;
use function is_string;
use function str_contains;

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
 *         // This will do the same as above but only if the handled uri starts with /robots
 *         $router->addResource("Robots", "/robots");
 *
 *         return $router;
 *     }
 * );
 * ```
 */
class Annotations extends Router
{
    use CamelizeTrait;
    use UncamelizeTrait;

    /**
     * @var string
     */
    protected string $actionSuffix = "Action";

    /**
     * @var callable|null
     */
    protected mixed $actionPreformatCallback = null;

    /**
     * @var string
     */
    protected string $controllerSuffix = "Controller";

    /**
     * @var array
     */
    protected array $handlers = [];

    /**
     * @var string
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
     * @return $this
     */
    public function addModuleResource(
        string $module,
        string $handler,
        ?string $prefix = null
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
        ?string $prefix = null
    ): Annotations {
        $this->handlers[] = [$prefix, $handler];

        return $this;
    }

    /**
     * @return callable|null
     */
    public function getActionPreformatCallback(): ?callable
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
     * @throws Exception
     * @throws EventsException
     */
    public function handle(string $uri): void
    {
        if (null === $this->container) {
            throw new Exception(
                "A dependency injection container is required to access the 'annotations' service"
            );
        }

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
                 * The lowercase class name is used as controller
                 * Extract the namespace from the namespaced class
                 */
                $handlerNameArray = explode("\\", $handler);

                // Extract the real class name from the namespaced class
                $controllerName = array_pop($handlerNameArray);

                // Extract the namespace from the namespaced class
                $namespaceName = implode("\\", $handlerNameArray);
            } else {
                $controllerName = $handler;
                $namespaceName  = $this->defaultNamespace;
            }

            $this->routePrefix = '';

            /**
             * Check if the scope has a module associated
             */
            $moduleName = $scope[2] ?? null;
            $moduleName = ($moduleName !== null) ? $moduleName : "";

            $sufixxed = $controllerName . $this->controllerSuffix;

            /**
             * Add namespace to class if one is set
             */
            if (null !== $namespaceName) {
                $sufixxed = $namespaceName . "\\" . $sufixxed;
            }

            /**
             * Get the annotations from the class
             */
            $handlerAnnotations = $annotationsService->get($sufixxed);

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

            if (is_object($methodAnnotations)) {
                $lowerControllerName = $this->toCamelize($controllerName);

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
     * @throws RouterException
     */
    public function processActionAnnotation(
        string $module,
        string $namespaceName,
        string $controller,
        string $action,
        Annotation $annotation
    ): void {
        $name = $annotation->getName();

        /**
         * Find if the route is for adding routes
         */
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
            "Options" => strtoupper($name),
            default   => null,
        };

        if (false === $isRoute) {
            return;
        }

        $proxyActionName = str_replace($this->actionSuffix, "", $action);
        $routePrefix     = $this->routePrefix;

        if (null !== $this->actionPreformatCallback) {
            $proxyActionName = call_user_func($this->actionPreformatCallback, $proxyActionName);
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
        $uri = $routePrefix;
        if (null !== $value && '/' !== $value) {
            $uri .= $value;
        } elseif (empty($routePrefix)) {
            $uri = $value;
        } else {
            $uri .= $actionName;
        }

        /**
         * Add the route to the router
         */
        $route = $this->add($uri, $paths);

        /**
         * Add HTTP constraint methods
         */
        if (null !== $methods) {
            $methods = $annotation->getNamedArgument("methods");
        }

        if (is_array($methods) || is_string($methods)) {
            $route->via($methods);
        }

        /**
         * Add the converters
         */
        $converters = $annotation->getNamedArgument("converts");

        if (is_array($converters)) {
            foreach ($converters as $parameter => $converter) {
                $route->convert($parameter, $converter);
            }
        }

        /**
         * Add the converters
         *
         * @todo why is this here twice?
         */
        $converters = $annotation->getNamedArgument("converters");

        if (is_array($converters)) {
            foreach ($converters as $parameter => $converter) {
                $route->convert($parameter, $converter);
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
    ) {
        /**
         * @RoutePrefix add a prefix for all the routes defined in the model
         */
        if ($annotation->getName() === "RoutePrefix") {
            $this->routePrefix = $annotation->getArgument(0);
        }
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
     * @param callable|null $callback
     *
     * @return string|void
     * @throws Exception
     */
    public function setActionPreformatCallback(?callable $callback = null)
    {
        if (is_callable($callback)) {
            $this->actionPreformatCallback = $callback;
        } elseif (null === $callback) {
            $this->actionPreformatCallback = function ($action) {
                return $this->toUncamelize($action, "-");
            };
        } else {
            throw new Exception(
                "The 'callback' parameter must be either a callable or NULL."
            );
        }
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
