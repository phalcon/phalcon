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
use Phalcon\Mvc\Url\Exception;
use Phalcon\Mvc\Url\UrlInterface;
use Phalcon\Parsers\Parser;

use function http_build_query;
use function is_array;
use function is_string;
use function preg_match;
use function preg_replace;
use function strlen;

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
        mixed $baseUri = null
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

                $this->router = $this->container->getShared("router");
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
            /**
             * @todo Check the implementation for this
             */
//            $uri = phalcon_replace_paths(
//                $route->getPattern(),
//                $route->getReversedPaths(),
//                $uri
//            );
            $uri = Parser::replacePaths(
                $route->getPattern(),
                $route->getReversedPaths(),
                $uri
            );
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
            $queryString = http_build_query($arguments);

            if (strlen($queryString)) {
                if (str_contains($uri, "?")) {
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
                /**
                 * @todo Check the implementation for this
                 */
                // $uri = phalcon_get_uri($_SERVER["PHP_SELF"]);
                $uri = $_SERVER["PHP_SELF"];
            } else {
                $uri = null;
            }

            if (null !== $uri) {
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
}
