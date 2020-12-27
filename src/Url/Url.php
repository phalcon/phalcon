<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace Phalcon\Url;

use Phalcon\Di\DiInterface;
use Phalcon\Di\AbstractInjectionAware;
use Phalcon\Mvc\RouterInterface;
use Phalcon\Mvc\Router\RouteInterface;
use Phalcon\Url\Exception;
use Phalcon\Url\UrlInterface;

/**
 * This components helps in the generation of: URIs, URLs and Paths
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
     * @var null | string
     */
    protected ?string $baseUri = null;

    /**
     * @var null | string
     */
    protected ?string $basePath = null;

    /**
     * @var RouterInterface | null
     */
    protected ?RouterInterface $router = null;

    /**
     * @var null | string
     */
    protected ?string $staticBaseUri = null;

    public function __construct( ?RouterInterface $router = null)
    {
        $this->router = $router;
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
     * @param array|string uri = [
     *     'for' => '',
     * ]
     */
    public function get($uri = null, $args = null, bool $local = null, $baseUri = null) : string
    {
        if ($local === null) {
            if(is_string($uri) && (strpos($uri,"//") !== false) || (strpos($uri, ":")!==false)) {
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

        if(is_array($uri)) {
            $routeName = $uri["for"] ?? null;
            if ($routeName===null) {
                throw new Exception(
                    "It's necessary to define the route name with the parameter 'for'"
                );
            }

            $router = $this->router;

            /**
             * Check if the router has not previously set
             */
            if ($router===null) {
                $container = $this->container;

                if (!is_object($container)){
                    throw new Exception(
                        Exception::containerServiceNotFound(
                            "the 'router' service"
                        )
                    );
                }

                if (!$container->has("router")) {
                    throw new Exception(
                        Exception::containerServiceNotFound(
                            "the 'router' service"
                        )
                    );
                }
                $router = $container->getShared("router");
                $this->router = $router;
            }

            /**
             * Every route is uniquely differenced by a name
             */
            $route = $router->getRouteByName($routeName);

            if (!is_object($route)) {
                throw new Exception(
                    "Cannot obtain a route using the name '" . $routeName . "'"
                );
            }

            /**
             * Replace the patterns by its variables
             */
            $uri = phalcon_replace_paths(
                $route->getPattern(),
                $route->getReversedPaths(),
                $uri
            );
        }

        if ($local !== null) {
            $strUri = (string) $uri;
            $uri = preg_replace("#(?<!:)//+#", "/", $baseUri . $strUri);
        }

        if ($args) {
           $queryString = http_build_query($args);

            if(is_string($queryString) && strlen($queryString)) {
                if (strpos($uri, "?") !== false) {
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
     */
    public function getBasePath() : string
    {
        return $this->basePath;
    }

    /**
     * Returns the prefix for all the generated urls. By default /
     */
    public function getBaseUri() : string
    {
        $baseUri = $this->baseUri;
        if ($baseUri === null) {
            $phpSelf = _SERVER["PHP_SELF"] ?? null;
            if ($phpSelf !== null)  {
                $uri = phalcon_get_uri($phpSelf);
            } else {
                $uri = null;
            }

            if($uri === null) {
                $baseUri = "/";
            } else {
                $baseUri = "/" . $uri ."/";
            }

            $this->baseUri = $baseUri;
        }

        return $baseUri;
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
     * @param array|string uri = [
     *     'for' => ''
     * ]
     */
    public function getStatic($uri = null) : string
    {
        return $this->get(
            $uri,
            null,
            null,
            $this->getStaticBaseUri()
        );
    }

    /**
     * Returns the prefix for all the generated static urls. By default /
     */
    public function getStaticBaseUri() : string
    {
        $staticBaseUri = $this->staticBaseUri;

        if ($staticBaseUri !== null) {
            return $staticBaseUri;
        }

        return $this->getBaseUri();
    }

    /**
     * Sets a base path for all the generated paths
     *
     *```php
     * $url->setBasePath("/var/www/htdocs/");
     *```
     */
    public function setBasePath(string  $basePath) : UrlInterface
    {
        $this->basePath = $basePath;

        return this;
    }

    /**
     * Sets a prefix for all the URIs to be generated
     *
     *```php
     * $url->setBaseUri("/invo/");
     *
     * $url->setBaseUri("/invo/index.php/");
     *```
     */
    public function setBaseUri(string $baseUri) : UrlInterface
    {
        $this->baseUri = $baseUri;

        if ($this->staticBaseUri === null) {
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
     */
    public function setStaticBaseUri(string $staticBaseUri) : UrlInterface
    {
        $this->staticBaseUri = $staticBaseUri;

        return $this;
    }

    /**
     * Generates a local path
     */
    public function path(string $path = null) : string
    {
        return $this->basePath . $path;
    }
}
