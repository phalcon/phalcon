<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Implementation of this file has been influenced by Nyholm/psr7 and Laminas
 *
 * @link    https://github.com/Nyholm/psr7
 * @license https://github.com/Nyholm/psr7/blob/master/LICENSE
 * @link    https://github.com/laminas/laminas-diactoros
 * @license https://github.com/zendframework/zend-diactoros/blob/master/LICENSE.md
 */

namespace Phalcon\Http\Message\Factories;

use Phalcon\Http\Message\Exception\InvalidArgumentException;
use Phalcon\Http\Message\Interfaces\RequestMethodInterface;
use Phalcon\Http\Message\Interfaces\ServerRequestFactoryInterface;
use Phalcon\Http\Message\Interfaces\ServerRequestInterface;
use Phalcon\Http\Message\Interfaces\UploadedFileInterface;
use Phalcon\Http\Message\Interfaces\UriInterface;
use Phalcon\Http\Message\ServerRequest;
use Phalcon\Http\Message\UploadedFile;
use Phalcon\Http\Message\Uri;
use Phalcon\Support\Collection;
use Phalcon\Support\Collection\CollectionInterface;

use function apache_request_headers;
use function explode;
use function function_exists;
use function implode;
use function is_array;
use function ltrim;
use function parse_str;
use function preg_match;
use function preg_replace;
use function str_replace;
use function strlen;
use function strtolower;
use function substr;

/**
 * Factory for ServerRequest objects
 */
class ServerRequestFactory implements
    ServerRequestFactoryInterface,
    RequestMethodInterface
{
    /**
     * Create a new server request.
     *
     * Note that server-params are taken precisely as given - no
     * parsing/processing of the given values is performed, and, in particular,
     * no attempt is made to determine the HTTP method or URI, which must be
     * provided explicitly.
     *
     * @param string              $method       The HTTP method associated with
     *                                          the request.
     * @param UriInterface|string $uri          The URI associated with the
     *                                          request. If the value is a
     *                                          string, the factory MUST create
     *                                          a UriInterface instance based
     *                                          on it.
     * @param array               $serverParams Array of SAPI parameters with
     *                                          which to seed the generated
     *                                          request instance.
     *
     * @return ServerRequestInterface
     */
    public function createServerRequest(
        string $method,
        $uri,
        array $serverParams = []
    ): ServerRequestInterface {
        return new ServerRequest($method, $uri, $serverParams);
    }

    /**
     * Create a request from the supplied superglobal values.
     *
     * If any argument is not supplied, the corresponding superglobal value will
     * be used.
     *
     * @param array|null $server  $_SERVER superglobal
     * @param array|null $get     $_GET superglobal
     * @param array|null $post    $_POST superglobal
     * @param array|null $cookies $_COOKIE superglobal
     * @param array|null $files   $_FILES superglobal
     *
     * @return ServerRequest
     */
    public function load(
        array | null $server = null,
        array | null $get = null,
        array | null $post = null,
        array | null $cookies = null,
        array | null $files = null
    ): ServerRequest {
        /**
         * Ensure that superglobals are defined if not
         */
        $globalCookies = !empty($_COOKIE) ? $_COOKIE : [];
        $globalFiles   = !empty($_FILES) ? $_FILES : [];
        $globalGet     = !empty($_GET) ? $_GET : [];
        $globalPost    = !empty($_POST) ? $_POST : [];
        $globalServer  = !empty($_SERVER) ? $_SERVER : [];

        $server            = $this->checkNullArray($server, $globalServer);
        $files             = $this->checkNullArray($files, $globalFiles);
        $cookies           = $this->checkNullArray($cookies, $globalCookies);
        $get               = $this->checkNullArray($get, $globalGet);
        $post              = $this->checkNullArray($post, $globalPost);
        $serverCollection  = $this->parseServer($server);
        $method            = $serverCollection->get(
            "REQUEST_METHOD",
            self::METHOD_GET
        );
        $protocol          = $this->parseProtocol($serverCollection);
        $headers           = $this->parseHeaders($serverCollection);
        $filesCollection   = $this->parseUploadedFiles($files);
        $cookiesCollection = $cookies;

        if (empty($cookies) && true === $headers->has("cookie")) {
            $cookiesCollection = $this->parseCookieHeader(
                $headers->get("cookie")
            );
        }

        return new ServerRequest(
            $method,
            $this->parseUri($serverCollection, $headers),
            $serverCollection->toArray(),
            "php://input",
            $headers->toArray(),
            $cookiesCollection,
            $get,
            $filesCollection->toArray(),
            $post,
            $protocol
        );
    }

    /**
     * Returns the apache_request_headers if it exists
     *
     * @return array|false
     */
    protected function getHeaders()
    {
        if (true === function_exists("apache_request_headers")) {
            return apache_request_headers();
        }

        return false;
    }

    /**
     * Calculates the host and port from the headers or the server superglobal
     *
     * @param CollectionInterface $server
     * @param CollectionInterface $headers
     *
     * @return array
     */
    private function calculateUriHost(
        CollectionInterface $server,
        CollectionInterface $headers
    ): array {
        $defaults = ["", null];

        if (false !== $this->getHeader($headers, "host", false)) {
            $host = $this->getHeader($headers, "host");

            return $this->calculateUriHostFromHeader($host);
        }

        if (true !== $server->has("SERVER_NAME")) {
            return $defaults;
        }

        $host = $server->get("SERVER_NAME");
        $port = $server->get("SERVER_PORT");

        return [$host, $port];
    }

    /**
     * Get the host and calculate the port if present from the header
     *
     * @param string $host
     *
     * @return array
     */
    private function calculateUriHostFromHeader(string $host): array
    {
        $port = null;

        // works for regname, IPv4 & IPv6
        if (preg_match("|:(\d+)$|", $host, $matches)) {
            $host = substr($host, 0, -1 * (strlen($matches[1]) + 1));
            $port = (int)$matches[1];
        }

        return [$host, $port];
    }

    /**
     * Get the path from the request from IIS7/Rewrite, REQUEST_URL or
     * ORIG_PATH_INFO
     *
     * @param CollectionInterface $server
     *
     * @return string
     */
    private function calculateUriPath(CollectionInterface $server): string
    {
        /**
         * IIS7 with URL Rewrite - double slash
         */
        $iisRewrite   = $server->get("IIS_WasUrlRewritten");
        $unencodedUrl = $server->get("UNENCODED_URL", "");

        if ("1" === $iisRewrite && !empty($unencodedUrl)) {
            return $unencodedUrl;
        }

        /**
         * REQUEST_URI
         */
        $requestUri = $server->get("REQUEST_URI");

        if (null !== $requestUri) {
            return preg_replace("#^[^/:]+://[^/]+#", "", $requestUri);
        }

        /**
         * ORIG_PATH_INFO
         */
        $origPathInfo = $server->get("ORIG_PATH_INFO");
        if (empty($origPathInfo)) {
            return "/";
        }

        return $origPathInfo;
    }

    /**
     * Get the query string from the server array
     *
     * @param CollectionInterface $server
     *
     * @return string
     */
    private function calculateUriQuery(CollectionInterface $server): string
    {
        return ltrim($server->get("QUERY_STRING", ""), "?");
    }

    /**
     * Calculates the scheme from the server variables
     *
     * @param CollectionInterface $server
     * @param CollectionInterface $headers
     *
     * @return string
     */
    private function calculateUriScheme(
        CollectionInterface $server,
        CollectionInterface $headers
    ): string {
        // URI scheme
        $scheme  = "https";
        $isHttps = true;
        if (true === $server->has("HTTPS")) {
            $isHttps = (string)$server->get("HTTPS", "on");
            $isHttps = "off" !== strtolower($isHttps);
        }

        $header = $this->getHeader($headers, "x-forwarded-proto", "https");
        if (!$isHttps || "https" !== $header) {
            $scheme = "http";
        }

        return $scheme;
    }

    /**
     * Checks the source if it is null and returns the super, otherwise the
     * source
     *
     * @param mixed $source
     * @param array $super
     *
     * @return array
     */
    private function checkNullArray($source, array $super): array
    {
        if (empty($source)) {
            return $super;
        }

        return $source;
    }

    /**
     * Create an UploadedFile object from an $_FILES array element
     *
     * @param array $file The $_FILES element
     *
     * @return UploadedFile
     *
     * @throws InvalidArgumentException If one of the elements is missing
     */
    private function createUploadedFile(array $file): UploadedFile
    {
        if (
            !isset($file["tmp_name"]) ||
            !isset($file["size"]) ||
            !isset($file["error"])
        ) {
            throw new InvalidArgumentException(
                "The file array must contain tmp_name, size and error; " .
                "one or more are missing"
            );
        }

        $name = isset($file["name"]) ? $file["name"] : null;
        $type = isset($file["type"]) ? $file["type"] : null;

        return new UploadedFile(
            $file["tmp_name"],
            $file["size"],
            $file["error"],
            $name,
            $type
        );
    }

    /**
     * Returns a header
     *
     * @param CollectionInterface $headers
     * @param string              $name
     * @param mixed|null          $defaultValue
     *
     * @return mixed|string
     */
    private function getHeader(
        CollectionInterface $headers,
        string $name,
        $defaultValue = null
    ) {
        $value = $headers->get($name, $defaultValue);

        if (is_array($value)) {
            $value = implode(",", $value);
        }

        return $value;
    }

    /**
     * Parse a cookie header according to RFC 6265.
     *
     * @param string $cookieHeader A string cookie header value.
     *
     * @return array key/value cookie pairs.
     */
    private function parseCookieHeader(string $cookieHeader): array
    {
        $cookies = [];
        parse_str(
            strtr(
                $cookieHeader,
                [
                    "&" => "%26",
                    "+" => "%2B",
                    ";" => "&",
                ]
            ),
            $cookies
        );

        return $cookies;
    }

    /**
     * Processes headers from SAPI
     *
     * @param CollectionInterface $server
     *
     * @return CollectionInterface
     */
    private function parseHeaders(
        CollectionInterface $server
    ): CollectionInterface {
        /**
         * @todo Figure out why server is not iterable
         */
        $headers     = new Collection();
        $serverArray = $server->toArray();

        foreach ($serverArray as $key => $value) {
            if ("" !== $value) {
                /**
                 * Apache prefixes environment variables with REDIRECT_
                 * if they are added by rewrite rules
                 */
                if (str_starts_with($key, "REDIRECT_")) {
                    $key = substr($key, 9);
                    /**
                     * We will not overwrite existing variables with the
                     * prefixed versions, though
                     */
                    if (true === $server->has($key)) {
                        continue;
                    }
                }

                if (str_starts_with($key, "HTTP_")) {
                    $name = str_replace(
                        "_",
                        "-",
                        strtolower(substr($key, 5))
                    );

                    $headers->set($name, $value);
                    continue;
                }

                if (str_starts_with($key, "CONTENT_")) {
                    $name = "content-" . strtolower(substr($key, 8));

                    $headers->set($name, $value);
                }
            }
        }

        return $headers;
    }

    /**
     * Parse the $_SERVER array amd check the server protocol. Raise an
     *
     * @param CollectionInterface $server The server variables
     *
     * @return string
     */
    private function parseProtocol(CollectionInterface $server): string
    {
        if (true !== $server->has("SERVER_PROTOCOL")) {
            return "1.1";
        }

        $protocol      = (string)$server->get("SERVER_PROTOCOL", "HTTP/1.1");
        $localProtocol = strtolower($protocol);
        $protocols     = [
            "1.0" => 1,
            "1.1" => 1,
            "2.0" => 1,
            "3.0" => 1,
        ];

        /**
         * 5 characters to distinguish between http and https
         */
        if (!str_starts_with($localProtocol, "http/")) {
            throw new InvalidArgumentException(
                "Incorrect protocol value " . $protocol
            );
        }

        $localProtocol = str_replace("http/", "", $localProtocol);

        if (!isset($protocols[$localProtocol])) {
            throw new InvalidArgumentException(
                "Unsupported protocol " . $protocol
            );
        }

        return $localProtocol;
    }

    /**
     * Parse the $_SERVER array amd return it back after looking for the
     * authorization header
     *
     * @param array $server Either verbatim, or with an added
     *                      HTTP_AUTHORIZATION header.
     *
     * @return CollectionInterface
     */
    private function parseServer(array $server): CollectionInterface
    {
        $collection = new Collection($server);
        $headers    = $this->getHeaders();

        if (
            true !== $collection->has("HTTP_AUTHORIZATION") &&
            false !== $headers
        ) {
            $headersCollection = new Collection($headers);

            if (true === $headersCollection->has("Authorization")) {
                $collection->set(
                    "HTTP_AUTHORIZATION",
                    $headersCollection->get("Authorization")
                );
            }
        }

        return $collection;
    }

    /**
     * Traverses a $_FILES and creates UploadedFile objects from it. It is used
     * recursively
     *
     * @param array $files
     *
     * @return CollectionInterface
     */
    private function parseUploadedFiles(array $files): CollectionInterface
    {
        $collection = new Collection();

        /**
         * Loop through the files and check them recursively
         */
        foreach ($files as $key => $file) {
            $key = (string)$key;

            /**
             * UriInterface
             */
            if ($file instanceof UploadedFileInterface) {
                $collection->set($key, $file);
                continue;
            }

            /**
             * file is array with 'tmp_name'
             */
            if (is_array($file) && isset($file["tmp_name"])) {
                $collection->set($key, $this->createUploadedFile($file));
                continue;
            }

            /**
             * file is array of elements - recursion
             */
            if (is_array($file)) {
                $data = $this->parseUploadedFiles($file);

                $collection->set($key, $data->toArray());
            }
        }

        return $collection;
    }

    /**
     * Calculates the Uri from the server superglobal or the headers
     *
     * @param CollectionInterface $server
     * @param CollectionInterface $headers
     *
     * @return Uri
     */
    private function parseUri(
        CollectionInterface $server,
        CollectionInterface $headers
    ): Uri {
        $uri = new Uri();

        /**
         * Scheme
         */
        $scheme = $this->calculateUriScheme($server, $headers);
        $uri    = $uri->withScheme($scheme);

        /**
         * Host/Port
         */
        $split = $this->calculateUriHost($server, $headers);

        if (!empty($split[0])) {
            $uri = $uri->withHost($split[0]);
            if (!empty($split[1])) {
                $uri = $uri->withPort($split[1]);
            }
        }

        /**
         * Path
         */
        $path  = $this->calculateUriPath($server);
        $split = explode("#", $path);
        $path  = explode("?", $split[0]);
        $uri   = $uri->withPath($path[0]);

        if (count($split) > 1) {
            $uri = $uri->withFragment($split[1]);
        }

        /**
         * Query
         */
        $query = $this->calculateUriQuery($server);

        return $uri->withQuery($query);
    }
}
