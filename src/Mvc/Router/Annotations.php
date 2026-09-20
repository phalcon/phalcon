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

use Phalcon\Annotations\Adapter\AdapterInterface;
use Phalcon\Annotations\Annotation;
use Phalcon\Events\Exception as EventsException;
use Phalcon\Mvc\Router;
use Phalcon\Mvc\Router\Exceptions\AnnotationsServiceUnavailable;
use Phalcon\Mvc\Router\Exceptions\InvalidCallbackParameter;
use Phalcon\Traits\Support\Helper\Str\UncamelizeTrait;

use function array_pop;
use function call_user_func;
use function explode;
use function implode;
use function is_array;
use function is_callable;
use function is_object;
use function is_string;
use function preg_match;
use function str_contains;
use function str_ends_with;
use function str_replace;
use function str_starts_with;
use function strlen;
use function strtolower;
use function strtoupper;
use function substr;

/**
 * Phalcon\Mvc\Router\Annotations
 *
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
 *         // This will do the same as above but only if the handled uri starts with /invoices
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
     * @phpstan-var callable|string|null
     */
    protected mixed $actionPreformatCallback = null;

    protected string $actionSuffix = "Action";

    protected string $controllerSuffix = "Controller";

    /**
     * @phpstan-var list<array{0: string|null, 1: string, 2?: string}>
     */
    protected array $handlers = [];

    protected string | null $routePrefix = "";

    /**
     * Adds a resource to the annotations handler
     * A resource is a class that contains routing annotations
     * The class is located in a module
     *
     * @phpstan-return static
     */
    public function addModuleResource(
        string $module,
        string $handler,
        string | null $prefix = null
    ): static {
        $this->handlers[] = [$prefix, $handler, $module];

        return $this;
    }

    /**
     * Adds a resource to the annotations handler
     * A resource is a class that contains routing annotations
     *
     * @phpstan-return static
     */
    public function addResource(
        string $handler,
        string | null $prefix = null
    ): static {
        $this->handlers[] = [$prefix, $handler];

        return $this;
    }

    /**
     * @return callable|string|null
     */
    public function getActionPreformatCallback()
    {
        return $this->actionPreformatCallback;
    }

    /**
     * Return the registered resources
     *
     * @phpstan-return list<array{0: string|null, 1: string, 2?: string}>
     */
    public function getResources(): array
    {
        return $this->handlers;
    }

    /**
     * Produce the routing parameters from the rewrite information
     *
     * @throws AnnotationsServiceUnavailable
     * @throws EventsException
     * @throws Exception
     */
    public function handle(string $uri): void
    {
        $container = $this->container;

        if (null === $container) {
            throw new AnnotationsServiceUnavailable();
        }

        $handlers         = $this->handlers;
        $controllerSuffix = $this->controllerSuffix;

        /** @var AdapterInterface $annotationsService */
        $annotationsService = $container->getShared("annotations");

        foreach ($handlers as $scope) {
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
                 * In that case We only need to theck if it starts with
                 * the pattern so we remove to "$" from the end.
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
                $controllerName      = (string) array_pop($controllerNameArray);
                $namespaceName       = implode("\\", $controllerNameArray);

                /**
                 * Strip the suffix if the FQCN already includes it,
                 * so we do not end up with e.g. "InvoicesControllerController"
                 */
                if (str_ends_with($controllerName, $controllerSuffix)) {
                    $controllerName = substr(
                        $controllerName,
                        0,
                        strlen($controllerName) - strlen($controllerSuffix)
                    );
                }
            } else {
                $controllerName = $handler;
                $namespaceName  = $this->defaultNamespace;
            }

            $this->routePrefix = null;

            /**
             * Check if the scope has a module associated
             */
            $moduleName = $scope[2] ?? null;
            $moduleName = $moduleName !== null ? $moduleName : "";

            $sufixed = $controllerName . $controllerSuffix;

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
     * @throws Exception
     */
    public function processActionAnnotation(
        string $module,
        string $namespaceName,
        string $controller,
        string $action,
        Annotation $annotation
    ): void {
        $isRoute = false;
        $methods = null;
        $name    = $annotation->getName();

        /**
         * Find if the route is for adding routes
         */
        switch ($name) {
            case "Route":
                $isRoute = true;
                break;

            case "Connect":
            case "Delete":
            case "Get":
            case "Head":
            case "Options":
            case "Patch":
            case "Post":
            case "Purge":
            case "Put":
            case "Trace":
                $isRoute = true;
                $methods = strtoupper((string) $name);
                break;
        }

        if (!$isRoute) {
            return;
        }

        $proxyActionName = str_replace($this->actionSuffix, "", $action);
        $routePrefix     = $this->routePrefix;

        if (null !== $this->actionPreformatCallback) {
            /** @var callable $preformatCallback */
            $preformatCallback = $this->actionPreformatCallback;
            $proxyActionName   = call_user_func(
                $preformatCallback,
                $proxyActionName
            );
        }

        /** @var string $proxyActionName */
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

        /** @var string|null $value */
        $value = $annotation->getArgument(0);

        /**
         * Create the route using the prefix
         */
        if ($value !== null) {
            if ($value != "/") {
                $uri = $routePrefix . $value;
            } else {
                if ($routePrefix !== null) {
                    $uri = $routePrefix;
                } else {
                    $uri = $value;
                }
            }
        } else {
            $uri = $routePrefix . $actionName;
        }

        /**
         * Add the route to the router
         *
         * @var array<string, int|string> $paths
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
                $route->convert((string) $param, $convert);
            }
        }

        /**
         * Add the converters
         */
        $converts = $annotation->getNamedArgument("converters");

        if (is_array($converts)) {
            foreach ($converts as $converterParam => $convert) {
                $route->convert((string) $converterParam, $convert);
            }
        }

        /**
         * Add the converters
         */
        $beforeMatch = $annotation->getNamedArgument("beforeMatch");

        if (is_array($beforeMatch) || is_string($beforeMatch)) {
            /**
             * The annotation names a function or a [class, method] pair.
             *
             * @var (array<array-key, mixed>|string)&callable $beforeMatch
             */
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
     * @return void
     */
    public function processControllerAnnotation(
        string $handler,
        Annotation $annotation
    ) {
        /**
         * @RoutePrefix add a prefix for all the routes defined in the model
         */
        if ($annotation->getName() == "RoutePrefix") {
            /** @var string|null $routePrefix */
            $routePrefix       = $annotation->getArgument(0);
            $this->routePrefix = $routePrefix;
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
     * @throws InvalidCallbackParameter
     *
     * @phpstan-return static
     */
    public function setActionPreformatCallback(mixed $callback = null): static
    {
        if (is_callable($callback)) {
            $this->actionPreformatCallback = $callback;
        } elseif ($callback === null) {
            $this->actionPreformatCallback = function (string $action): string {
                return $this->toUncamelize($action, "-");
            };
        } else {
            throw new InvalidCallbackParameter();
        }

        return $this;
    }

    /**
     * Changes the action method suffix
     *
     * @phpstan-return static
     */
    public function setActionSuffix(string $actionSuffix): static
    {
        $this->actionSuffix = $actionSuffix;

        return $this;
    }

    /**
     * Changes the controller class suffix
     *
     * @phpstan-return static
     */
    public function setControllerSuffix(string $controllerSuffix): static
    {
        $this->controllerSuffix = $controllerSuffix;

        return $this;
    }
}
