<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace Phalcon\Cli\Router;

use Phalcon\Support\RouteHelper;
use function get_class_ns;

/**
 * This class represents every route added to the router
 */
class Route implements RouteInterface {

    const DEFAULT_DELIMITER = " ";
    protected static $delimiterPath = self::DEFAULT_DELIMITER;
    
    protected $beforeMatch;
    protected $compiledPattern;
    protected $converters;
    protected string $delimiter;
    
    protected ?string $description;
    protected int $id;
    protected ?string $name;
    protected $paths;
    protected string $pattern;

    /**
     * @param array|string paths
     */
    public function __construct(string $pattern, $paths = null) {
        // Get the delimiter from the static member delimiterPath
        $this->delimiter = self::$delimiterPath;

        // Configure the route (extract parameters, paths, etc)
        $this->reConfigure($pattern, $paths);

        // TODO: Add a function that increase static members
        $this->id = RouteHelper::nextRouteId();
    }

    /**
     * Sets a callback that is called if the route is matched.
     * The developer can implement any arbitrary conditions here
     * If the callback returns false the route is treated as not matched
     *
     * @param callback callback
     */
    public function beforeMatch($callback): RouteInterface {
        $this->beforeMatch = $callback;

        return $this;
    }

    /**
     * Replaces placeholders from pattern returning a valid PCRE regular
     * expression
     */
    public function compilePattern(string $pattern): string {
        if (strpos($pattern, ":") !== false) {

            // This is a pattern for valid identifiers
            $idPattern = $this->delimiter . "([a-zA-Z0-9\\_\\-]+)";

            // Replace the delimiter part
            if (strpos($pattern, ":delimiter") !== false) {
                $pattern = str_replace(
                        ":delimiter",
                        $this->delimiter,
                        $pattern
                );
            }

            // Replace the module part
            $part = $this->delimiter . ":module";
            if (strpos($pattern, $part) !== false) {
                $pattern = str_replace($part, $idPattern, $pattern);
            }

            // Replace the task placeholder
            $part = $this->delimiter . ":task";
            if (strpos($pattern, $part) !== false) {
                $pattern = str_replace($part, $idPattern, $pattern);
            }

            // Replace the namespace placeholder
            $part = $this->delimiter . ":namespace";
            if (strpos($pattern, $part) !== false) {
                $pattern = str_replace($part, $idPattern, $pattern);
            }

            // Replace the action placeholder
            $part = $this->delimiter . ":action";
            if (strpos($pattern, $part) !== false) {
                $pattern = str_replace($part, $idPattern, $pattern);
            }

            // Replace the params placeholder
            $part = $this->delimiter . ":params";
            if (strpos($pattern, $part) !== false) {
                $pattern = str_replace(
                        $part,
                        "(" . $this->delimiter . ".*)*",
                        $pattern
                );
            }

            // Replace the int placeholder
            $part = $this->delimiter . ":int";
            if (strpos($pattern, $part) !== false) {
                $pattern = str_replace(
                        $part,
                        $this->delimiter . "([0-9]+)",
                        $pattern
                );
            }
        }

        /**
         * Check if the pattern has parentheses or square brackets in order to
         * add the regex delimiters
         */
        if ((strpos($pattern, "(") !== false) || (strpos($pattern, "[") !== false)) {
            return "#^" . $pattern . "$#";
        }

        return $pattern;
    }

    /**
     * Adds a converter to perform an additional transformation for certain
     * parameter
     *
     * @param callable converter
     */
    public function convert(string $name, $converter): RouteInterface {
        $this->converters[$name] = $converter;

        return $this;
    }

    /**
     * Set the routing delimiter
     */
    public static function delimiter(string $delimiter = null) {
        self::$delimiterPath = $delimiter;
    }


    /**
     * Returns the 'before match' callback if any
     */
    public function getBeforeMatch() {
        return $this->beforeMatch;
    }

    /**
     * Returns the route's compiled pattern
     */
    public function getCompiledPattern(): string {
        return $this->compiledPattern;
    }

    /**
     * Returns the router converter
     */
    public function getConverters(): array {
        return $this->converters;
    }

    /**
     * Get routing delimiter
     */
    public static function getDelimiter(): string {
        return self::$delimiterPath;
    }

    /**
     * Returns the route's description
     */
    public function getDescription(): ?string {
        return $this->description;
    }

    /**
     * Returns the route's name
     */
    public function getName(): ?string {
        return $this->name;
    }

    /**
     * Returns the paths
     */
    public function getPaths(): array {
        return $this->paths;
    }

    /**
     * Returns the route's pattern
     */
    public function getPattern(): string {
        return $this->pattern;
    }

    /**
     * Returns the paths using positions as keys and names as values
     */
    public function getReversedPaths(): array {
        return array_flip(
                $this->paths
        );
    }

    /**
     * Returns the route's id
     */
    public function getRouteId(): int {
        return $this->id;
    }

    /**
     * Reconfigure the route adding a new pattern and a set of paths
     *
     * @param array|string paths
     */
    public function reConfigure(string $pattern, $paths = null) {
        global $gUnCamelize;
        if ($paths === null) {
            $paths = [];
        }

        if (is_string($paths)) {
            $moduleName = null;
            $taskName = null;
            $actionName = null;

            // Explode the short paths using the :: separator
            $parts = explode("::", $paths);

            // Create the array paths dynamically
            switch (count($parts)) {

                case 3:
                    $moduleName = parts[0];
                    $taskName = parts[1];
                    $actionName = parts[2];
                    break;

                case 2:
                    $taskName = parts[0];
                    $actionName = parts[1];
                    break;

                case 1:
                    $taskName = parts[0];
                    break;
            }

            $routePaths = [];

            // Process module name
            if ($moduleName !== null) {
                $routePaths["module"] = $moduleName;
            }

            // Process task name
            if ($taskName !== null) {
                // Check if we need to obtain the namespace
                if (strpos($taskName, "\\") !== false) {
                    // Extract the real class name from the namespaced class
                    $realClassName = get_class_ns(taskName);

                    // Extract the namespace from the namespaced class
                    $namespaceName = get_ns_class(taskName);

                    if ($namespaceName === null || $realClassName === null) {
                        throw new Exception(
                                        "The route contains invalid paths"
                        );
                    }

                    // Update the namespace
                    if ($namespaceName) {
                        $routePaths["namespace"] = $namespaceName;
                    }
                } else {
                    $realClassName = $taskName;
                }

                // Always pass the task to lowercase
                $routePaths["task"] = $gUnCamelize(realClassName);
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

        /**
         * If the route starts with '#' we assume that it is a regular
         * expression
         */
        if (str_starts_with($pattern, "#")) {
            if (strpos($pattern, "{") !== false) {
                /**
                 * The route has named parameters so we need to extract them
                 */
                $extracted = RouteHelper::extractParams($pattern);
                if (!empty($extracted)) {
                    $pcrePattern = $extracted[0];
                    $routePaths = array_merge($routePaths, $extracted[1]);
                }
            } else {
                $pcrePattern = $pattern;
            }

            /**
             * Transform the route's pattern to a regular expression
             */
            $compiledPattern = $this->compilePattern($pcrePattern);
        } else {
            // Replace the delimiter part
            if (strpos($pattern, ":delimiter") !== false) {
                $pattern = str_replace(
                        ":delimiter",
                        $this->delimiter,
                        $pattern
                );
            }

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
     */
    public static function reset() {
        RouteHelper::reset();
    }

    /**
     * Sets the route's description
     */
    public function setDescription(string $description): RouteInterface {
        $this->description = $description;

        return $this;
    }

    /**
     * Sets the route's name
     *
     * ```php
     * $router->add(
     *     "/about",
     *     [
     *         "controller" => "about",
     *     ]
     * )->setName("about");
     * ```
     */
    public function setName(string $name): RouteInterface {
        $this->name = $name;

        return $this;
    }

}
