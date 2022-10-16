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

namespace Phalcon\Cli\Router;

use Phalcon\Traits\Helper\Str\UncamelizeTrait;

use function array_keys;
use function explode;
use function is_array;
use function str_contains;
use function str_replace;
use function str_split;
use function str_starts_with;

/**
 * This class represents every route added to the router
 */
class Route implements RouteInterface
{
    use UncamelizeTrait;

    public const DEFAULT_DELIMITER = " ";

    /**
     * @var mixed|null
     */
    protected mixed $beforeMatch = null;

    /**
     * @var string|null
     */
    protected ?string $compiledPattern = null;

    /**
     * @var array
     */
    protected array $converters = [];

    /**
     * @var string
     */
    protected string $delimiter;

    /**
     * @var string
     */
    protected static string $delimiterPath = self::DEFAULT_DELIMITER;

    /**
     * @var string|null
     */
    protected string $description = "";

    /**
     * @var string
     */
    protected string $routeId;

    /**
     * @var string|null
     */
    protected string $name = "";

    /**
     * @var array
     */
    protected array $paths = [];

    /**
     * @var string
     */
    protected string $pattern;

    /**
     * @var int
     */
    protected static int $uniqueId = 0;

    /**
     * Constructor
     *
     * @param string       $pattern
     * @param array|string $paths
     */
    public function __construct(string $pattern, array|string $paths = [])
    {
        // Get the delimiter from the static member delimiterPath
        $this->delimiter = self::$delimiterPath;

        // Configure the route (extract parameters, paths, etc)
        $this->reConfigure($pattern, $paths);

        // Get the unique Id from the static member uniqueId
        $uniqueId = self::$uniqueId;

        // TODO: Add a function that increase static members
        $this->routeId  = (string) $uniqueId;
        self::$uniqueId = $uniqueId + 1;
    }

    /**
     * Sets a callback that is called if the route is matched.
     * The developer can implement any arbitrary conditions here
     * If the callback returns false the route is treated as not matched
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
     * Replaces placeholders from pattern returning a valid PCRE regular
     * expression
     *
     * @param string $pattern
     *
     * @return string
     */
    public function compilePattern(string $pattern): string
    {
        // If a pattern contains ':', maybe there are placeholders to replace
        if (str_contains($pattern, ":")) {

            // This is a pattern for valid identifiers
            $idPattern = $this->delimiter . "([a-zA-Z0-9\\_\\-]+)";
            $map = [
                ":delimiter"                    => $this->delimiter,
                $this->delimiter . ":module"    => $idPattern,
                $this->delimiter . ":task"      => $idPattern,
                $this->delimiter . ":namespace" => $idPattern,
                $this->delimiter . ":action"    => $idPattern,
                $this->delimiter . ":params"    => "(" . $this->delimiter . ".*)*",
                $this->delimiter . ":int"       => $this->delimiter . "([0-9]+)",
            ];

            $pattern = str_replace(
                array_keys($map),
                array_values($map),
                $pattern
            );
        }

        /**
         * Check if the pattern has parentheses or square brackets in order to
         * add the regex delimiters
         */
        if (str_contains($pattern, "(") || str_contains($pattern, "[")) {
            return "#^" . $pattern . "$#";
        }

        return $pattern;
    }

    /**
     * Adds a converter to perform an additional transformation for certain
     * parameter
     *
     * @param string   $name
     * @param callable $converter
     *
     * @return RouteInterface
     */
    public function convert(string $name, callable $converter): RouteInterface
    {
        $this->converters[$name] = $converter;

        return $this;
    }

    /**
     * Set the routing delimiter
     *
     * @param string $delimiter
     *
     * @return void
     */
    public static function delimiter(string $delimiter): void
    {
        self::$delimiterPath = $delimiter;
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
        if (0 === strlen($pattern)) {
            return false;
        }

        $matches          = [];
        $route            = "";
        $bracketCount     = 0;
        $parenthesesCount = 0;
        $intermediate     = 0;
        $numberMatches    = 0;

        $patternArray = str_split($pattern);
        foreach ($patternArray as $cursor => $character) {
            if (0 === $parenthesesCount) {
                if ('{' === $character) {
                    if (0 === $bracketCount) {
                        $marker       = $cursor + 1;
                        $intermediate = 0;
                        $notValid     = false;
                    }

                    $bracketCount++;
                } elseif ('}' === $character) {
                    $bracketCount--;

                    if ($intermediate > 0) {
                        if (0 === $bracketCount) {
                            $numberMatches++;
                            $variable = null;
                            $regexp   = null;
                            $item     = substr(
                                $pattern,
                                $marker,
                                $cursor - $marker
                            );

                            $itemArray = str_split($item);
                            foreach ($itemArray as $cursorVar => $itemChar) {
                                if ('\0' === $itemChar) {
                                    break;
                                }

                                if (
                                    0 === $cursorVar &&
                                    !(
                                        ($itemChar >= 'a' && $itemChar <= 'z') ||
                                        ($itemChar >= 'A' && $itemChar <= 'Z')
                                    )
                                ) {
                                    $notValid = true;

                                    break;
                                }

                                if (
                                    ($itemChar >= 'a' && $itemChar <= 'z') ||
                                    ($itemChar >= 'A' && $itemChar <= 'Z') ||
                                    ($itemChar >= '0' && $itemChar <='9') ||
                                    $itemChar == '-' ||
                                    $itemChar == '_' ||
                                    $itemChar ==  ':'
                                ) {
                                    if (':' === $itemChar) {
                                        $variable = (string) substr($item, 0, $cursorVar);
                                        $regexp   = (string) substr($item, $cursorVar + 1);

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
                                    $regexpArray = str_split($regexp);
                                    foreach ($regexpArray as $regexChar) {
                                        if ('\0' === $regexChar) {
                                            break;
                                        }

                                        if (!$foundPattern) {
                                            if ('(' === $regexChar) {
                                                $foundPattern = 1;
                                            }
                                        } elseif (')' === $regexChar) {
                                            $foundPattern = 2;

                                            break;
                                        }
                                    }

                                    if ($foundPattern !== 2) {
                                        $route .= "(" . $regexp . ")";
                                    } else {
                                        $route .= $regexp;
                                    }

                                    $matches[$variable] = $tmp;
                                } else {
                                    $route         .= "([^" . $this->delimiter . "]*)";
                                    $matches[$item] = $tmp;
                                }
                            } else {
                                $route .= "{" . $item . "}";
                            }

                            continue;
                        }
                    }
                }
            }

            if (0 === $bracketCount) {
                if ('(' === $character) {
                    $parenthesesCount++;
                } elseif (')' === $character) {
                    $parenthesesCount--;

                    if (0 === $parenthesesCount) {
                        $numberMatches++;
                    }
                }
            }

            if ($bracketCount > 0) {
                $intermediate++;
            } else {
                $route .= $character;
            }
        }

        return [$route, $matches];
    }

    /**
     * Returns the 'before match' callback if any
     *
     * @return mixed
     */
    public function getBeforeMatch(): mixed
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
     * Get routing delimiter
     *
     * @return string
     */
    public static function getDelimiter(): string
    {
        return self::$delimiterPath;
    }

    /**
     * Returns the route's description
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Returns the route's name
     *
     * @return string
     */
    public function getName(): string
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
        return array_flip($this->paths);
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
     * Reconfigure the route adding a new pattern and a set of paths
     *
     * @param string       $pattern
     * @param array|string $paths
     *
     * @return void
     * @throws Exception
     */
    public function reConfigure(string $pattern, array|string $paths = []): void
    {
        if (is_string($paths)) {
            $moduleName = null;
            $taskName = null;
            $actionName = null;

            // Explode the short paths using the :: separator
            $parts = explode("::", $paths);
            $countParts = count($parts);

            // Create the array paths dynamically
            switch ($countParts) {
                case 3:
                    $moduleName = $parts[0];
                    $taskName   = $parts[1];
                    $actionName = $parts[2];
                    break;

                case 2:
                    $taskName   = $parts[0];
                    $actionName = $parts[1];
                    break;

                case 1:
                    $taskName = $parts[0];
                    break;
            }

            $routePaths = [];

            // Process module name
            if (null !== $moduleName) {
                $routePaths["module"] = $moduleName;
            }

            // Process task name
            if (null !== $taskName) {
                // Check if we need to obtain the namespace
                if (str_contains($taskName, "\\")) {
                    $taskNameArray = explode("\\", $taskName);

                    // Extract the namespace from the namespaced class
                    $realClassName = array_pop($taskNameArray);

                    // Extract the real class name from the namespaced class
                    $namespaceName = implode("\\", $taskNameArray);

                    if (null === $namespaceName || null === $realClassName) {
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
                $routePaths["task"] = $this->toUncamelize($realClassName);
            }

            // Process action name
            if (null !== $actionName) {
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
        if (!str_starts_with($pattern, "#")) {
            if (str_contains($pattern, "{")) {
                /**
                 * The route has named parameters so we need to extract them
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
            // Replace the delimiter part
            if (str_contains($pattern, ":delimiter")) {
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
     *
     * @return void
     */
    public static function reset(): void
    {
        self::$uniqueId = 0;
    }

    /**
     * Sets the route's description
     *
     * @param string $description
     *
     * @return RouteInterface
     */
    public function setDescription(string $description): RouteInterface
    {
        $this->description = $description;

        return $this;
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
}
