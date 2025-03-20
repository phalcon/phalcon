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

use function array_flip;
use function array_merge;
use function array_pop;
use function explode;
use function implode;
use function is_array;
use function is_string;
use function str_replace;
use function str_split;
use function strlen;
use function substr;

/**
 * This class represents every route added to the router
 */
class Route implements RouteInterface
{
    /**
     * @mixed $callable|null
     */
    protected mixed $beforeMatch = null;

    /**
     * @mixed string|null
     */
    protected string | null $compiledPattern = null;

    /**
     * @mixed array
     */
    protected array $converters = [];

    /**
     * @mixed GroupInterface|null
     */
    protected GroupInterface | null $group = null;

    /**
     * @mixed string|null
     */
    protected string | null $hostname = null;
    /**
     * @mixed callable|null
     */
    protected mixed $match = null;
    /**
     * @mixed array|string|null
     */
    protected array | string | null $methods = [];
    /**
     * @mixed string|null
     */
    protected string | null $name = null;
    /**
     * @mixed array
     */
    protected array $paths = [];
    /**
     * @mixed string
     */
    protected string $pattern = '';
    /**
     * @mixed string
     */
    protected string $routeId = "";
    /**
     * @mixed $int
     */
    protected static int $uniqueId = 0;

    /**
     * Phalcon\Mvc\Router\Route constructor
     *
     * @param string            $pattern
     * @param array|string|null $paths
     * @param array|string|null $httpMethods
     *
     * @throws Exception
     */
    public function __construct(
        string $pattern,
        array | string | null $paths = null,
        array | string | null $httpMethods = null
    ) {
        // Configure the route (extract parameters, paths, etc)
        $this->reConfigure($pattern, $paths);

        // Update the HTTP method constraints
        $this->via($httpMethods);

        // Get the unique Id from the static member uniqueId
        $uniqueId = self::$uniqueId;

        // TODO: Add a function that increase static members
        $this->routeId  = (string)$uniqueId;
        self::$uniqueId = $uniqueId + 1;
    }

    /**
     * Sets a callback that is called if the route is matched.
     * The developer can implement any arbitrary conditions here
     * If the callback returns false the route is treated as not matched
     *
     *```php
     * $router->add(
     *     "/login",
     *     [
     *         "module"     => "admin",
     *         "controller" => "session",
     *     ]
     * )->beforeMatch(
     *     function ($uri, $route) {
     *         // Check if the request was made with Ajax
     *         if ($_SERVER["HTTP_X_REQUESTED_WITH"] === "xmlhttprequest") {
     *             return false;
     *         }
     *
     *         return true;
     *     }
     * );
     *```
     *
     * @param callable $callback
     *
     * @return RouteInterface
     */
    public function beforeMatch(callable $callback): RouteInterface
    {
        $this->beforeMatch = $callback;

        return $this;
    }

    /**
     * Replaces placeholders from pattern returning a valid PCRE regular expression
     */
    public function compilePattern(string $pattern): string
    {
        // If a pattern contains ':', maybe there are placeholders to replace
        if (str_contains($pattern, ":")) {
            $idPattern = "/([\\w0-9\\_\\-]+)";
            $map       = [
                "/:module"     => $idPattern,
                "/:controller" => $idPattern,
                "/:namespace"  => $idPattern,
                "/:action"     => $idPattern,
                "/:params"     => "(/.*)*",
                "/:int"        => "/([0-9]+)",
            ];

            $pattern = str_replace(array_keys($map), array_values($map), $pattern);
        }

        /**
         * Check if the pattern has parentheses or square brackets in order to
         * add the regex delimiters
         *
         * `u` flag is required to support unicode
         */
        if (str_contains($pattern, "(") || str_contains($pattern, "[")) {
            return "#^" . $pattern . "$#u";
        }

        return $pattern;
    }

    /**
     * @param string $name
     * @param mixed  $converter
     *
     * @return RouteInterface
     */
    public function convert(string $name, mixed $converter): RouteInterface
    {
        $this->converters[$name] = $converter;

        return $this;
    }

    /**
     * Extracts parameters from a string
     *
     * @param string $pattern
     *
     * @return array|bool
     */
    public function extractNamedParams(string $pattern): array | bool
    {
        $bracketCount     = 0;
        $intermediate     = 0;
        $marker           = 0;
        $numberMatches    = 0;
        $notValid         = false;
        $parenthesesCount = 0;
        $prevCh           = '\0';

        if (strlen($pattern) === 0) {
            return false;
        }

        $matches = [];
        $route   = "";

        $arrayPattern = str_split($pattern);
        foreach ($arrayPattern as $cursor => $ch) {
            if ($parenthesesCount === 0) {
                if ($ch === '{') {
                    if ($bracketCount === 0) {
                        $marker       = $cursor + 1;
                        $intermediate = 0;
                        $notValid     = false;
                    }

                    $bracketCount++;
                } elseif ($ch === '}') {
                    $bracketCount--;

                    if ($intermediate > 0 && $bracketCount === 0) {
                        $numberMatches++;
                        $variable = null;
                        $regexp   = null;
                        $item     = substr($pattern, $marker, $cursor - $marker);

                        $arrayItem = str_split($item);
                        foreach ($arrayItem as $cursorVar => $char) {
                            if ($char == '\0') {
                                break;
                            }

                            if (
                                $cursorVar === 0 &&
                                !(
                                    ($char >= 'a' && $char <= 'z') ||
                                    ($char >= 'A' && $char <= 'Z')
                                )
                            ) {
                                $notValid = true;

                                break;
                            }

                            if (
                                ($char >= 'a' && $char <= 'z') ||
                                ($char >= 'A' && $char <= 'Z') ||
                                ($char >= '0' && $char <= '9') ||
                                $char == '-' ||
                                $char == '_' ||
                                $char == ':'
                            ) {
                                if ($char == ':') {
                                    $variable = substr($item, 0, $cursorVar);
                                    $regexp   = substr($item, $cursorVar + 1);

                                    break;
                                }
                            } else {
                                $notValid = true;

                                break;
                            }
                        }

                        if (!$notValid) {
                            $tmp = $numberMatches;

                            if ($variable && $regexp) {
                                $foundPattern = 0;
                                $arrayRegexp  = str_split($regexp);
                                foreach ($arrayRegexp as $char) {
                                    if ($char === '\0') {
                                        break;
                                    }

                                    if (!$foundPattern) {
                                        if ($char === '(') {
                                            $foundPattern = 1;
                                        }
                                    } else {
                                        if ($char === ')') {
                                            $foundPattern = 2;

                                            break;
                                        }
                                    }
                                }

                                if ($foundPattern !== 2) {
                                    $route .= "(" . $regexp . ")";
                                } else {
                                    $route .= $regexp;
                                }

                                $matches[$variable] = $tmp;
                            } else {
                                $route          .= "([^/]*)";
                                $matches[$item] = $tmp;
                            }
                        } else {
                            $route .= "{" . $item . "}";
                        }

                        continue;
                    }
                }
            }

            if ($bracketCount === 0) {
                if ($ch === '(') {
                    $parenthesesCount++;
                } elseif ($ch === ')') {
                    $parenthesesCount--;

                    if ($parenthesesCount === 0) {
                        $numberMatches++;
                    }
                }
            }

            if ($bracketCount > 0) {
                $intermediate++;
            } else {
                if (
                    $parenthesesCount === 0 && $prevCh !== '\\' && ($ch === '.' ||
                        $ch === '+' ||
                        $ch === '|' ||
                        $ch === '#')
                ) {
                    $route .= '\\';
                }

                $route  .= $ch;
                $prevCh = $ch;
            }
        }

        return [$route, $matches];
    }

    /**
     * Returns the 'before match' callback if any
     *
     * @return callable|null
     */
    public function getBeforeMatch(): callable | null
    {
        return $this->beforeMatch;
    }

    /**
     * Returns the route's compiled pattern
     *
     * @return string
     */
    public function getCompiledPattern(): string
    {
        return $this->compiledPattern;
    }

    /**
     * Returns the router converter
     *
     * @return array
     */
    public function getConverters(): array
    {
        return $this->converters;
    }

    /**
     * Returns the group associated with the route
     *
     * @return GroupInterface|null
     */
    public function getGroup(): GroupInterface | null
    {
        return $this->group;
    }

    /**
     * Returns the hostname restriction if any
     */
    public function getHostname(): string | null
    {
        return $this->hostname;
    }

    /**
     * Returns the HTTP methods that constraint matching the route
     *
     * @return array|string|null
     */
    public function getHttpMethods(): array | string | null
    {
        return $this->methods;
    }

    /**
     * Returns the 'match' callback if any
     *
     * @return callable|null
     */
    public function getMatch(): callable | null
    {
        return $this->match;
    }

    /**
     * Returns the route's name
     *
     * @return string|null
     */
    public function getName(): string | null
    {
        return $this->name;
    }

    /**
     * Returns the paths
     *
     * @return array
     */
    public function getPaths(): array
    {
        return $this->paths;
    }

    /**
     * Returns the route's pattern
     *
     * @return string
     */
    public function getPattern(): string
    {
        return $this->pattern;
    }

    /**
     * Returns the paths using positions as keys and names as values
     *
     * @return array
     */
    public function getReversedPaths(): array
    {
        return array_flip(
            $this->paths
        );
    }

    /**
     * Returns the route's id
     *
     * @return string
     */
    public function getRouteId(): string
    {
        return $this->routeId;
    }

    /**
     * Returns routePaths
     *
     * @param array|string|null $paths
     *
     * @return array
     * @throws Exception
     */
    public static function getRoutePaths(array | string | null $paths = null): array
    {
        if ($paths === null) {
            $paths = [];
        }

        if (is_string($paths)) {
            $moduleName     = null;
            $controllerName = null;
            $actionName     = null;

            // Explode the short paths using the :: separator
            $parts = explode("::", $paths);

            // Create the array paths dynamically
            switch (count($parts)) {
                case 3:
                    $moduleName     = $parts[0];
                    $controllerName = $parts[1];
                    $actionName     = $parts[2];
                    break;

                case 2:
                    $controllerName = $parts[0];
                    $actionName     = $parts[1];
                    break;

                case 1:
                    $controllerName = $parts[0];
                    break;
            }

            $routePaths = [];

            // Process module name
            if ($moduleName !== null) {
                $routePaths["module"] = $moduleName;
            }

            // Process controller name
            if ($controllerName !== null) {
                // Check if we need to obtain the namespace
                if (str_contains($controllerName, "\\")) {
                    $controllerNameArray = explode("\\", $controllerName);

                    // Extract the real class name from the namespaced class
                    $realClassName = array_pop($controllerNameArray);

                    // Extract the namespace from the namespaced class
                    $namespaceName = implode("\\", $controllerNameArray);

                    // Update the namespace
                    if ($namespaceName) {
                        $routePaths["namespace"] = $namespaceName;
                    }
                } else {
                    $realClassName = $controllerName;
                }

                $routePaths["controller"] = $realClassName;
            }

            // Process action name
            if ($actionName !== null) {
                $routePaths["action"] = $actionName;
            }
        } else {
            $routePaths = $paths;
        }

        if (!is_array($routePaths)) {
            throw new Exception("The route contains invalid paths");
        }

        return $routePaths;
    }

    /**
     * Allows to set a callback to handle the request directly in the route
     *
     *```php
     * $router->add(
     *     "/help",
     *     []
     * )->match(
     *     function () {
     *         return $this->getResponse()->redirect("https://support.google.com/", true);
     *     }
     * );
     *```
     */
    public function match(callable $callback): RouteInterface
    {
        $this->match = $callback;

        return $this;
    }

    /**
     * Reconfigure the route adding a new pattern and a set of paths
     *
     * @param string            $pattern
     * @param array|string|null $paths
     *
     * @return void
     * @throws Exception
     */
    public function reConfigure(
        string $pattern,
        array | string | null $paths = null
    ): void {
        $routePaths = self::getRoutePaths($paths);

        /**
         * If the route starts with '#' we assume that it is a regular expression
         */
        if (!str_starts_with($pattern, "#")) {
            if (str_contains($pattern, "{")) {
                /**
                 * The route has named parameters, so we need to extract them
                 */
                $extracted   = $this->extractNamedParams($pattern);
                $pcrePattern = $extracted[0];
                $routePaths  = array_merge($routePaths, $extracted[1]);
            } else {
                $pcrePattern = $pattern;
            }

            /**
             * Transform the route's pattern to a regular expression
             */
            $compiledPattern = $this->compilePattern($pcrePattern);
        } else {
            $compiledPattern = $pattern;
        }

        /**
         * Update the original pattern
         */
        $this->pattern = $pattern;

        /**
         * Update the compiled pattern
         */
        $this->compiledPattern = $compiledPattern;

        /**
         * Update the route's paths
         */
        $this->paths = $routePaths;
    }

    /**
     * Resets the internal route id generator
     *
     * @return void
     */
    public static function reset(): void
    {
        self::$uniqueId = 0;
    }

    /**
     * Sets the group associated with the route
     *
     * @param GroupInterface $group
     *
     * @return RouteInterface
     */
    public function setGroup(GroupInterface $group): RouteInterface
    {
        $this->group = $group;

        return $this;
    }

    /**
     * Sets a hostname restriction to the route
     *
     *```php
     * $route->setHostname("localhost");
     *```
     *
     * @param string $hostname
     *
     * @return RouteInterface
     */
    public function setHostname(string $hostname): RouteInterface
    {
        $this->hostname = $hostname;

        return $this;
    }

    /**
     * Sets a set of HTTP methods that constraint the matching of the route (alias of via)
     *
     *```php
     * $route->setHttpMethods("GET");
     *
     * $route->setHttpMethods(
     *     [
     *         "GET",
     *         "POST",
     *     ]
     * );
     *```
     *
     * @param array|string $httpMethods
     *
     * @return RouteInterface
     */
    public function setHttpMethods(array | string $httpMethods): RouteInterface
    {
        return $this->via($httpMethods);
    }

    /**
     * Sets the route's name
     *
     *```php
     * $router->add(
     *     "/about",
     *     [
     *         "controller" => "about",
     *     ]
     * )->setName("about");
     *```
     *
     * @param string $name
     *
     * @return RouteInterface
     */
    public function setName(string $name): RouteInterface
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Set one or more HTTP methods that constraint the matching of the route
     *
     *```php
     * $route->via("GET");
     *
     * $route->via(
     *     [
     *         "GET",
     *         "POST",
     *     ]
     * );
     *```
     *
     * @param array|string|null $httpMethods
     *
     * @return RouteInterface
     */
    public function via(array | string | null $httpMethods): RouteInterface
    {
        $this->methods = $httpMethods;

        return $this;
    }
}
