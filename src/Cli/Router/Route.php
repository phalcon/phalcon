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

use Phalcon\Cli\Router\Exceptions\BeforeMatchNotCallable;
use Phalcon\Cli\Router\Exceptions\InvalidRoutePaths;
use Phalcon\Contracts\Cli\CliTypes;
use Phalcon\Traits\Support\Helper\Str\UncamelizeTrait;

use function array_flip;
use function array_keys;
use function array_merge;
use function array_pop;
use function array_values;
use function explode;
use function implode;
use function is_array;
use function is_callable;
use function is_string;
use function str_replace;
use function str_split;
use function strlen;
use function substr;

/**
 * This class represents every route added to the router
 *
 * @phpstan-import-type cli_route_converters from CliTypes
 * @phpstan-import-type cli_route_extracted from CliTypes
 * @phpstan-import-type cli_route_paths from CliTypes
 * @phpstan-import-type cli_route_reversed_paths from CliTypes
 */
class Route implements RouteInterface
{
    use UncamelizeTrait;

    /**
     * @var string
     */
    public const DEFAULT_DELIMITER = " ";
    protected static ?string $delimiterPath = self::DEFAULT_DELIMITER;
    protected static int $uniqueId = 0;

    /**
     * @var mixed|null
     */
    protected mixed $beforeMatch = null;
    protected string $compiledPattern = "";
    /**
     * @phpstan-var cli_route_converters
     */
    protected array $converters = [];
    protected ?string $delimiter;
    protected string $description = "";
    protected string $name = "";
    /**
     * @phpstan-var cli_route_paths
     */
    protected array $paths = [];
    protected string $pattern = "";
    protected string $routeId;

    /**
     * Constructor
     *
     * @phpstan-param mixed $paths
     */
    public function __construct(string $pattern, mixed $paths = null)
    {
        // Get the delimiter from the static member delimiterPath
        $this->delimiter = self::$delimiterPath;

        // Configure the route (extract parameters, paths, etc)
        $this->reConfigure($pattern, $paths);

        // Get the unique Id from the static member uniqueId
        $uniqueId = self::$uniqueId;

        // TODO: Add a function that increase static members
        $this->routeId  = (string)$uniqueId;
        self::$uniqueId = $uniqueId + 1;
    }

    /**
     * Set the routing delimiter.
     *
     * This sets a process-global delimiter that each route captures at
     * construction time. Configure it once during bootstrap, before any routes
     * are created: routes built before and after a change keep their own
     * delimiter, and `Console::setArgument()` reads the current value when it
     * parses arguments.
     */
    public static function delimiter(?string $delimiter = null): void
    {
        self::$delimiterPath = $delimiter;
    }

    /**
     * Get routing delimiter
     */
    public static function getDelimiter(): ?string
    {
        return self::$delimiterPath;
    }

    /**
     * Resets the internal route id generator.
     *
     * Intended for test isolation only. The router keys its route map by the
     * route id, so resetting the sequence while a router still holds routes
     * makes newly created routes overwrite existing entries.
     */
    public static function reset(): void
    {
        self::$uniqueId = 0;
    }

    /**
     * Sets a callback that is called if the route is matched.
     * The developer can implement any arbitrary conditions here
     * If the callback returns false the route is treated as not matched
     *
     * @param mixed $callback
     */
    public function beforeMatch(mixed $callback): RouteInterface
    {
        if (!is_callable($callback)) {
            throw new BeforeMatchNotCallable($this->pattern);
        }

        $this->beforeMatch = $callback;

        return $this;
    }

    /**
     * Replaces placeholders from pattern returning a valid PCRE regular
     * expression
     */
    public function compilePattern(string $pattern): string
    {
        $delimiter = (string) $this->delimiter;

        // If a pattern contains ':', maybe there are placeholders to replace
        if (str_contains($pattern, ":")) {
            // This is a pattern for valid identifiers
            $idPattern = $delimiter . "([a-zA-Z0-9\\_\\-]+)";
            $map       = [
                ":delimiter"                 => $delimiter,
                $delimiter . ":module"       => $idPattern,
                $delimiter . ":task"         => $idPattern,
                $delimiter . ":namespace"    => $idPattern,
                $delimiter . ":action"       => $idPattern,
                $delimiter . ":params"       => "(" . $delimiter . ".*)?",
                $delimiter . ":int"          => $delimiter . "([0-9]+)",
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
     * @param callable $converter
     */
    public function convert(string $name, $converter): RouteInterface
    {
        $this->converters[$name] = $converter;

        return $this;
    }

    /**
     * Extracts parameters from a string
     *
     * @phpstan-return cli_route_extracted|false
     */
    public function extractNamedParams(string $pattern): array | bool
    {
        if (0 === strlen($pattern)) {
            return false;
        }

        $bracketCount     = 0;
        $intermediate     = 0;
        $marker           = 0;
        $matches          = [];
        $notValid         = false;
        $numberMatches    = 0;
        $parenthesesCount = 0;
        $route            = "";

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

                    if ($intermediate > 0 && 0 === $bracketCount) {
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
                                ($itemChar >= '0' && $itemChar <= '9') ||
                                $itemChar == '-' ||
                                $itemChar == '_' ||
                                $itemChar == ':'
                            ) {
                                if (':' === $itemChar) {
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
                                $regexpArray  = str_split($regexp);
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
                                $route          .= "([^" . $this->delimiter . "]*)";
                                $matches[$item] = $tmp;
                            }
                        } else {
                            $route .= "{" . $item . "}";
                        }

                        continue;
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
     */
    public function getBeforeMatch(): mixed
    {
        return $this->beforeMatch;
    }

    /**
     * Returns the route's compiled pattern
     */
    public function getCompiledPattern(): string
    {
        return $this->compiledPattern;
    }

    /**
     * Returns the router converter
     */
    /**
     * @phpstan-return cli_route_converters
     */
    public function getConverters(): array
    {
        return $this->converters;
    }

    /**
     * Returns the route's description
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Returns the route's name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Returns the paths
     */
    /**
     * @phpstan-return cli_route_paths
     */
    public function getPaths(): array
    {
        return $this->paths;
    }

    /**
     * Returns the route's pattern
     */
    public function getPattern(): string
    {
        return $this->pattern;
    }

    /**
     * Returns the paths using positions as keys and names as values
     */
    /**
     * @phpstan-return cli_route_reversed_paths
     */
    public function getReversedPaths(): array
    {
        return array_flip($this->paths);
    }

    /**
     * Returns the route's id
     */
    public function getRouteId(): string
    {
        return $this->routeId;
    }

    /**
     * Reconfigure the route adding a new pattern and a set of paths
     *
     * @phpstan-param mixed $paths
     */
    public function reConfigure(string $pattern, $paths = null): void
    {
        if (null === $paths) {
            $paths = [];
        }

        if (is_string($paths)) {
            $moduleName = null;
            $taskName   = null;
            $actionName = null;

            // Explode the short paths using the :: separator
            $parts      = explode("::", $paths);
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

                    // Extract the real class name from the namespaced class
                    $realClassName = array_pop($taskNameArray);

                    // Extract the namespace from the namespaced class
                    $namespaceName = implode("\\", $taskNameArray);

                    if (empty($realClassName)) {
                        throw new InvalidRoutePaths($pattern);
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
            throw new InvalidRoutePaths($pattern);
        }

        /**
         * If the route starts with '#' we assume that it is a regular
         * expression
         */
        if (!str_starts_with($pattern, "#")) {
            if (str_contains($pattern, "{")) {
                /**
                 * The route has named parameters, so we need to extract them
                 */
                /** @phpstan-var cli_route_extracted $extracted */
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
                    (string) $this->delimiter,
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
        /** @phpstan-var cli_route_paths $routePaths */
        $this->paths = $routePaths;
    }

    /**
     * Sets the route's description
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
     */
    public function setName(string $name): RouteInterface
    {
        $this->name = $name;

        return $this;
    }
}
