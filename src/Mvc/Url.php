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

namespace Phalcon\Mvc;

use Phalcon\Di\AbstractInjectionAware;
use Phalcon\Di\DiInterface;
use Phalcon\Mvc\Url\Exception;
use Phalcon\Mvc\Url\UrlInterface;

use function array_merge;
use function http_build_query;
use function is_array;
use function is_string;
use function parse_str;
use function preg_match;
use function preg_replace;
use function strlen;
use function strpos;
use function substr;

/**
 * This component helps in the generation of: URIs, URLs and Paths
 *
 *```php
 * // Generate a URL appending the URI to the base URI
 * echo $url->get("products/edit/1");
 *
 * // Generate a URL for a predefined route
 * echo $url->get(
 *     [
 *         "for"   => "blog-post",
 *         "title" => "some-cool-stuff",
 *         "year"  => "2012",
 *     ]
 * );
 *```
 */
class Url extends AbstractInjectionAware implements UrlInterface
{
    /**
     * @var string|null
     */
    protected string | null $basePath = null;
    /**
     * @var string|null
     */
    protected string | null $baseUri = null;
    /**
     * @var string|null
     */
    protected string | null $staticBaseUri = null;

    public function __construct(
        protected RouterInterface | null $router = null
    ) {
    }

    /**
     * Generates a URL
     *
     *```php
     * // Generate a URL appending the URI to the base URI
     * echo $url->get("products/edit/1");
     *
     * // Generate a URL for a predefined route
     * echo $url->get(
     *     [
     *         "for"   => "blog-post",
     *         "title" => "some-cool-stuff",
     *         "year"  => "2015",
     *     ]
     * );
     *
     * // Generate a URL with GET arguments (/show/products?id=1&name=Carrots)
     * echo $url->get(
     *     "show/products",
     *     [
     *         "id"   => 1,
     *         "name" => "Carrots",
     *     ]
     * );
     *
     * // Generate an absolute URL by setting the third parameter as false.
     * echo $url->get(
     *     "https://phalcon.io/",
     *     null,
     *     false
     * );
     *```
     *
     * @param array|string|null $uri = [
     *                               'for' => '',
     *                               ]
     * @param mixed|null        $arguments
     * @param bool|null         $local
     * @param mixed|null        $baseUri
     *
     * @return string
     * @throws Exception
     */
    public function get(
        array | string | null $uri = null,
        mixed $arguments = null,
        ?bool $local = null,
        mixed $baseUri = null,
        bool $replaceArgs = false
    ): string {
        if (null === $local) {
            if (
                is_string($uri) &&
                (str_contains($uri, "//") || str_contains($uri, ":"))
            ) {
                if (preg_match("#^((//)|([a-z0-9]+://)|([a-z0-9]+:))#i", $uri)) {
                    $local = false;
                } else {
                    $local = true;
                }
            } else {
                $local = true;
            }
        }

        if (!is_string($baseUri)) {
            $baseUri = $this->getBaseUri();
        }

        if (is_array($uri)) {
            if (!isset($uri["for"])) {
                throw new Exception(
                    "It's necessary to define the route name with the parameter 'for'"
                );
            }

            $routeName = $uri["for"];

            /**
             * Check if the router has not previously set
             */
            if (null === $this->router) {
                if (null === $this->container) {
                    throw new Exception(
                        "A dependency injection container is "
                        . "required to access the 'router' service"
                    );
                }

                if (true !== $this->container->has("router")) {
                    throw new Exception(
                        "A dependency injection container is "
                        . "required to access the 'router' service"
                    );
                }

                if ($this->container instanceof DiInterface) {
                    $this->router = $this->container->getShared("router");
                } else {
                    $this->router = $this->container->get("router");
                }
            }

            /**
             * Every route is uniquely identified by a name
             */
            $route = $this->router->getRouteByName($routeName);

            if (false === $route) {
                throw new Exception(
                    "Cannot obtain a route using the name '" . $routeName . "'"
                );
            }

            /**
             * Replace the patterns by its variables
             */
            $uri = $this->replacePaths(
                $route->getPattern(),
                $route->getReversedPaths(),
                $uri
            );

            /**
             * If the route has a hostname restriction, prepend it as a
             * protocol-relative URL so the generated link works under
             * both HTTP and HTTPS. The baseUri is not prepended in this
             * case because the hostname already provides the authority.
             */
            $hostname = $route->getHostname();

            if (!empty($hostname)) {
                $uri   = '//' . $hostname . (substr($uri, 0, 1) !== '/' ? '/' . $uri : $uri);
                $local = false;
            }
        }

        if (true === $local) {
            $strUri = (string)$uri;
            $uri    = preg_replace(
                "#(?<!:)//+#",
                "/",
                $baseUri . $strUri
            );
        }

        if ($arguments) {
            $queryPos = strpos($uri, "?");

            if ($replaceArgs && $queryPos !== false) {
                $existing = [];

                parse_str(
                    (string)substr($uri, $queryPos + 1),
                    $existing
                );

                $arguments = array_merge($existing, (array)$arguments);
                $uri       = (string)substr($uri, 0, $queryPos);
                $queryPos  = false;
            }

            $queryString = http_build_query($arguments);

            if (strlen($queryString)) {
                if ($queryPos !== false) {
                    $uri .= "&" . $queryString;
                } else {
                    $uri .= "?" . $queryString;
                }
            }
        }

        return $uri;
    }

    /**
     * Returns the base path
     *
     * @return string|null
     */
    public function getBasePath(): string | null
    {
        return $this->basePath;
    }

    /**
     * Returns the prefix for all the generated urls. By default, /
     *
     * @return string
     */
    public function getBaseUri(): string
    {
        if (null === $this->baseUri) {
            if (isset($_SERVER["PHP_SELF"])) {
                $uri = $this->getUri($_SERVER["PHP_SELF"]);
            } else {
                $uri = null;
            }

            if (empty($uri)) {
                $baseUri = "/";
            } else {
                $baseUri = "/" . $uri . "/";
            }

            $this->baseUri = $baseUri;
        }

        return $this->baseUri;
    }

    /**
     * Generates a URL for a static resource
     *
     *```php
     * // Generate a URL for a static resource
     * echo $url->getStatic("img/logo.png");
     *
     * // Generate a URL for a static predefined route
     * echo $url->getStatic(
     *     [
     *         "for" => "logo-cdn",
     *     ]
     * );
     *```
     *
     * @param array|string|null $uri = [
     *                               'for' => ''
     *                               ]
     *
     * @return string
     * @throws Exception
     */
    public function getStatic(array | string | null $uri = null): string
    {
        return $this->get(
            $uri,
            null,
            null,
            $this->getStaticBaseUri()
        );
    }

    /**
     * Returns the prefix for all the generated static urls. By default, /
     *
     * @return string
     */
    public function getStaticBaseUri(): string
    {
        if (null !== $this->staticBaseUri) {
            return $this->staticBaseUri;
        }

        return $this->getBaseUri();
    }

    /**
     * Generates a local path
     *
     * @param string|null $path
     *
     * @return string
     */
    public function path(string | null $path = null): string
    {
        return $this->basePath . $path;
    }

    /**
     * Sets a base path for all the generated paths
     *
     *```php
     * $url->setBasePath("/var/www/htdocs/");
     *```
     *
     * @param string $basePath
     *
     * @return UrlInterface
     */
    public function setBasePath(string $basePath): UrlInterface
    {
        $this->basePath = $basePath;

        return $this;
    }

    /**
     * Sets a prefix for all the URIs to be generated
     *
     *```php
     * $url->setBaseUri("/invo/");
     *
     * $url->setBaseUri("/invo/index.php/");
     *```
     *
     * @param string $baseUri
     *
     * @return UrlInterface
     */
    public function setBaseUri(string $baseUri): UrlInterface
    {
        $this->baseUri = $baseUri;

        if (null === $this->staticBaseUri) {
            $this->staticBaseUri = $baseUri;
        }

        return $this;
    }

    /**
     * Sets a prefix for all static URLs generated
     *
     *```php
     * $url->setStaticBaseUri("/invo/");
     *```
     *
     * @param string $staticBaseUri
     *
     * @return UrlInterface
     */
    public function setStaticBaseUri(string $staticBaseUri): UrlInterface
    {
        $this->staticBaseUri = $staticBaseUri;

        return $this;
    }

    /**
     * Extracts the directory component between the last two path separators.
     *
     * Port of the C function phalcon_get_uri() from ext/phalcon/mvc/url/utils.c.
     *
     * For example: "/var/www/app/index.php" returns "app"
     *              "/index.php"             returns ""
     *
     * @param string $path
     *
     * @return string
     */
    private function getUri(string $path): string
    {
        $length = strlen($path);
        if ($length === 0) {
            return '';
        }

        $found = 0;
        $mark  = 0;

        for ($i = $length; $i > 0; $i--) {
            $ch = $path[$i - 1];
            if ($ch === '/' || $ch === '\\') {
                $found++;
                if ($found === 1) {
                    $mark = $i - 1;
                } else {
                    return substr($path, $i, $mark - $i);
                }
            }
        }

        return '';
    }

    /**
     * Looks up the replacement value for a placeholder in the pattern.
     *
     * Port of the C function phalcon_replace_marker() from ext/phalcon/mvc/url/utils.c.
     *
     * @param bool   $named        true for {name} placeholders, false for (:pos) / :word
     * @param array  $paths        reversed-paths map (position => name)
     * @param array  $replacements user-supplied key=>value replacements
     * @param int    $position     current positional counter (passed by reference)
     * @param string $pattern      the full route pattern string
     * @param int    $markerPos    index of the opening delimiter ({ or ( or :)
     * @param int    $cursorPos    index of the closing delimiter (} or ) or first non-alpha)
     *
     * @return string|null  the replacement value, or null if none found
     */
    private function replaceMarker(
        bool $named,
        array $paths,
        array $replacements,
        int &$position,
        string $pattern,
        int $markerPos,
        int $cursorPos
    ): string | null {
        $notValid = false;
        $item     = null;
        $variable = null;

        if ($named) {
            // Extract text between { and }: from markerPos+1 up to (not including) cursorPos
            $length = $cursorPos - $markerPos - 1;
            $item   = substr($pattern, $markerPos + 1, $length);

            for ($j = 0; $j < $length; $j++) {
                $ch = $item[$j];
                if ($j === 0 && !(($ch >= 'a' && $ch <= 'z') || ($ch >= 'A' && $ch <= 'Z'))) {
                    $notValid = true;
                    break;
                }
                if (
                    ($ch >= 'a' && $ch <= 'z') || ($ch >= 'A' && $ch <= 'Z') ||
                    ($ch >= '0' && $ch <= '9') || $ch === '-' || $ch === '_' || $ch === ':'
                ) {
                    if ($ch === ':') {
                        $variable = substr($item, 0, $j);
                        break;
                    }
                } else {
                    $notValid = true;
                    break;
                }
            }
        }

        if (!$notValid) {
            if (array_key_exists($position, $paths)) {
                if ($named) {
                    $key = $variable ?? $item;
                    if (array_key_exists($key, $replacements)) {
                        $position++;
                        return (string)$replacements[$key];
                    }
                } else {
                    $pathName = $paths[$position];
                    if (is_string($pathName) && array_key_exists($pathName, $replacements)) {
                        $position++;
                        return (string)$replacements[$pathName];
                    }
                }
            }
            $position++;
        }

        return null;
    }

    /**
     * Replaces placeholders in a route pattern with values from $replacements.
     *
     * Supports three placeholder styles:
     *   {name}   — named (curly-brace) placeholders
     *   (...)    — positional (parentheses) placeholders
     *   :word    — colon-prefixed placeholders
     *
     * Port of the C function phalcon_replace_paths() from ext/phalcon/mvc/url/utils.c.
     *
     * @param string $pattern      the route pattern (e.g. "/blog/{year}/{month}/{title}")
     * @param array  $paths        reversed-paths map (position => name)
     * @param mixed  $replacements user-supplied key=>value replacements (array portion used)
     *
     * @return string|false|null
     */
    private function replacePaths(
        string $pattern,
        array $paths,
        mixed $replacements
    ): string | false | null {
        if (!is_array($replacements)) {
            return null;
        }

        $len = strlen($pattern);
        if ($len === 0) {
            return false;
        }

        $i = ($pattern[0] === '/') ? 1 : 0;

        if (empty($paths)) {
            return substr($pattern, $i);
        }

        $bracketCount       = 0;
        $parenthesesCount   = 0;
        $intermediate       = 0;
        $lookingPlaceholder = false;
        $position           = 1;
        $routeStr           = '';
        $markerPos          = 0;

        for (; $i < $len; $i++) {
            $ch = $pattern[$i];

            if ($parenthesesCount === 0 && !$lookingPlaceholder) {
                if ($ch === '{') {
                    if ($bracketCount === 0) {
                        $markerPos    = $i;
                        $intermediate = 0;
                    }

                    $bracketCount++;
                } elseif ($ch === '}') {
                    $bracketCount--;
                    if ($intermediate > 0 && $bracketCount === 0) {
                        $replace = $this->replaceMarker(
                            true,
                            $paths,
                            $replacements,
                            $position,
                            $pattern,
                            $markerPos,
                            $i
                        );

                        if ($replace !== null) {
                            $routeStr .= $replace;
                        }

                        continue;
                    }
                }
            }

            if ($bracketCount === 0 && !$lookingPlaceholder) {
                if ($ch === '(') {
                    if ($parenthesesCount === 0) {
                        $markerPos    = $i;
                        $intermediate = 0;
                    }
                    $parenthesesCount++;
                } elseif ($ch === ')') {
                    $parenthesesCount--;
                    if ($intermediate > 0 && $parenthesesCount === 0) {
                        $replace = $this->replaceMarker(
                            false,
                            $paths,
                            $replacements,
                            $position,
                            $pattern,
                            $markerPos,
                            $i
                        );

                        if ($replace !== null) {
                            $routeStr .= $replace;
                        }

                        continue;
                    }
                }
            }

            if ($bracketCount === 0 && $parenthesesCount === 0) {
                if ($lookingPlaceholder) {
                    if ($intermediate > 0) {
                        if ($ch < 'a' || $ch > 'z' || $i === ($len - 1)) {
                            $replace = $this->replaceMarker(
                                false,
                                $paths,
                                $replacements,
                                $position,
                                $pattern,
                                $markerPos,
                                $i
                            );

                            if ($replace !== null) {
                                $routeStr .= $replace;
                            }
                            $lookingPlaceholder = false;

                            if ($ch < 'a' || $ch > 'z') {
                                $routeStr .= $ch;
                            }

                            continue;
                        }
                    }
                } else {
                    if ($ch === ':') {
                        $lookingPlaceholder = true;
                        $markerPos          = $i;
                        $intermediate       = 0;
                    }
                }
            }

            if ($bracketCount > 0 || $parenthesesCount > 0 || $lookingPlaceholder) {
                $intermediate++;
            } else {
                $routeStr .= $ch;
            }
        }

        return $routeStr;
    }
}
