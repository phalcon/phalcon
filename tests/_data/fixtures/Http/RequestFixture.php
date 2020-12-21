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

namespace Phalcon\Tests\Fixtures\Http;


use Phalcon\Http\RequestInterface;
use stdClass;

class RequestFixture implements RequestInterface
{
    /**
     * @var array
     */
    private array $store = [];

    /**
     * RequestFixture constructor.
     *
     * @param array $store
     */
    public function __construct(array $store = [])
    {
        $this->store = $store;
    }
    /**
     * Gets a variable from the $_REQUEST superglobal applying filters if
     * needed. If no parameters are given the $_REQUEST superglobal is returned
     *
     *```php
     * // Returns value from $_REQUEST["user_email"] without sanitizing
     * $userEmail = $request->get("user_email");
     *
     * // Returns value from $_REQUEST["user_email"] with sanitizing
     * $userEmail = $request->get("user_email", "email");
     *```
     *
     * @param string|null $name
     * @param mixed|null  $filters
     * @param mixed|null  $default
     * @param bool        $notAllowEmpty
     * @param bool        $noRecursive
     *
     * @return mixed
     */
    public function get(
        string $name = null,
        $filters = null,
        $default = null,
        bool $notAllowEmpty = false,
        bool $noRecursive = false
    ) {
        if (null === $name) {
            return $this->store;
        }

        return $this->store[$name] ?? $default;
    }

    /**
     * Gets an array with mime/types and their quality accepted by the
     * browser/client from _SERVER["HTTP_ACCEPT"]
     *
     * @return array
     */
    public function getAcceptableContent(): array
    {
        // TODO: Implement getAcceptableContent() method.
    }

    /**
     * Gets auth info accepted by the browser/client from
     * $_SERVER["PHP_AUTH_USER"]
     *
     * @return array|null
     */
    public function getBasicAuth(): ?array
    {
        // TODO: Implement getBasicAuth() method.
    }

    /**
     * Gets best mime/type accepted by the browser/client from
     * _SERVER["HTTP_ACCEPT"]
     *
     * @return string
     */
    public function getBestAccept(): string
    {
        // TODO: Implement getBestAccept() method.
    }

    /**
     * Gets best charset accepted by the browser/client from
     * _SERVER["HTTP_ACCEPT_CHARSET"]
     *
     * @return string
     */
    public function getBestCharset(): string
    {
        // TODO: Implement getBestCharset() method.
    }

    /**
     * Gets best language accepted by the browser/client from
     * _SERVER["HTTP_ACCEPT_LANGUAGE"]
     *
     * @return string
     */
    public function getBestLanguage(): string
    {
        // TODO: Implement getBestLanguage() method.
    }

    /**
     * Gets most possible client IPv4 Address. This method searches in
     * $_SERVER["REMOTE_ADDR"] and optionally in
     * $_SERVER["HTTP_X_FORWARDED_FOR"]
     *
     * @param bool $trustForwardedHeader
     *
     * @return string|bool
     */
    public function getClientAddress(bool $trustForwardedHeader = false)
    {
        // TODO: Implement getClientAddress() method.
    }

    /**
     * Gets a charsets array and their quality accepted by the browser/client
     * from _SERVER["HTTP_ACCEPT_CHARSET"]
     *
     * @return array
     */
    public function getClientCharsets(): array
    {
        // TODO: Implement getClientCharsets() method.
    }

    /**
     * Gets content type which request has been made
     *
     * @return string|null
     */
    public function getContentType(): ?string
    {
        // TODO: Implement getContentType() method.
    }

    /**
     * Gets auth info accepted by the browser/client from
     * $_SERVER["PHP_AUTH_DIGEST"]
     *
     * @return array
     */
    public function getDigestAuth(): array
    {
        // TODO: Implement getDigestAuth() method.
    }

    /**
     * Gets HTTP header from request data
     *
     * @param string $header
     *
     * @return string
     */
    public function getHeader(string $header): string
    {
        // TODO: Implement getHeader() method.
    }

    /**
     * Returns the available headers in the request
     *
     * ```php
     * $_SERVER = [
     *     "PHP_AUTH_USER" => "phalcon",
     *     "PHP_AUTH_PW"   => "secret",
     * ];
     *
     * $headers = $request->getHeaders();
     *
     * echo $headers["Authorization"]; // Basic cGhhbGNvbjpzZWNyZXQ=
     * ```
     *
     * @return array
     */
    public function getHeaders(): array
    {
        // TODO: Implement getHeaders() method.
    }

    /**
     * Gets host name used by the request.
     *
     * `Request::getHttpHost` trying to find host name in following order:
     *
     * - `$_SERVER["HTTP_HOST"]`
     * - `$_SERVER["SERVER_NAME"]`
     * - `$_SERVER["SERVER_ADDR"]`
     *
     * Optionally `Request::getHttpHost` validates and clean host name.
     * The `Request::$strictHostCheck` can be used to validate host name.
     *
     * Note: validation and cleaning have a negative performance impact because
     * they use regular expressions.
     *
     * ```php
     * use Phalcon\Http\Request;
     *
     * $request = new Request;
     *
     * $_SERVER["HTTP_HOST"] = "example.com";
     * $request->getHttpHost(); // example.com
     *
     * $_SERVER["HTTP_HOST"] = "example.com:8080";
     * $request->getHttpHost(); // example.com:8080
     *
     * $request->setStrictHostCheck(true);
     * $_SERVER["HTTP_HOST"] = "ex=am~ple.com";
     * $request->getHttpHost(); // UnexpectedValueException
     *
     * $_SERVER["HTTP_HOST"] = "ExAmPlE.com";
     * $request->getHttpHost(); // example.com
     * ```
     *
     * @return string
     */
    public function getHttpHost(): string
    {
        // TODO: Implement getHttpHost() method.
    }

    /**
     * Gets web page that refers active request. ie: http://www.google.com
     *
     * @return string
     */
    public function getHTTPReferer(): string
    {
        // TODO: Implement getHTTPReferer() method.
    }

    /**
     * Gets decoded JSON HTTP raw request body
     *
     * @param bool $associative
     *
     * @return stdClass|array|bool
     */
    public function getJsonRawBody(bool $associative = false)
    {
        // TODO: Implement getJsonRawBody() method.
    }

    /**
     * Gets languages array and their quality accepted by the browser/client
     * from _SERVER["HTTP_ACCEPT_LANGUAGE"]
     *
     * @return array
     */
    public function getLanguages(): array
    {
        // TODO: Implement getLanguages() method.
    }

    /**
     * Gets HTTP method which request has been made
     *
     * If the X-HTTP-Method-Override header is set, and if the method is a POST,
     * then it is used to determine the "real" intended HTTP method.
     *
     * The _method request parameter can also be used to determine the HTTP
     * method, but only if setHttpMethodParameterOverride(true) has been called.
     *
     * The method is always an uppercased string.
     *
     * @return string
     */
    public function getMethod(): string
    {
        // TODO: Implement getMethod() method.
    }

    /**
     * Gets information about the port on which the request is made
     *
     * @return int
     */
    public function getPort(): int
    {
        // TODO: Implement getPort() method.
    }

    /**
     * Gets HTTP URI which request has been made to
     *
     *```php
     * // Returns /some/path?with=queryParams
     * $uri = $request->getURI();
     *
     * // Returns /some/path
     * $uri = $request->getURI(true);
     *```
     *
     * @param bool $onlyPath If true, query part will be omitted
     *
     * @return string
     */
    public function getURI(bool $onlyPath = false): string
    {
        // TODO: Implement getURI() method.
    }

    /**
     * Gets a variable from the $_POST superglobal applying filters if needed
     * If no parameters are given the $_POST superglobal is returned
     *
     *```php
     * // Returns value from $_POST["user_email"] without sanitizing
     * $userEmail = $request->getPost("user_email");
     *
     * // Returns value from $_POST["user_email"] with sanitizing
     * $userEmail = $request->getPost("user_email", "email");
     *```
     *
     * @param string|null $name
     * @param mixed|null  $filters
     * @param mixed|null  $default
     * @param bool        $notAllowEmpty
     * @param bool        $noRecursive
     *
     * @return mixed
     */
    public function getPost(
        string $name = null,
        $filters = null,
        $default = null,
        bool $notAllowEmpty = false,
        bool $noRecursive = false
    ) {
        if (null === $name) {
            return $this->store;
        }

        return $this->store[$name] ?? $default;
    }

    /**
     * Gets a variable from put request
     *
     *```php
     * // Returns value from $_PUT["user_email"] without sanitizing
     * $userEmail = $request->getPut("user_email");
     *
     * // Returns value from $_PUT["user_email"] with sanitizing
     * $userEmail = $request->getPut("user_email", "email");
     *```
     *
     * @param string|null $name
     * @param mixed|null  $filters
     * @param mixed|null  $default
     * @param bool        $notAllowEmpty
     * @param bool        $noRecursive
     *
     * @return mixed
     */
    public function getPut(string $name = null, $filters = null, $default = null, bool $notAllowEmpty = false, bool $noRecursive = false)
    {
        // TODO: Implement getPut() method.
    }

    /**
     * Gets variable from $_GET superglobal applying filters if needed
     * If no parameters are given the $_GET superglobal is returned
     *
     *```php
     * // Returns value from $_GET["id"] without sanitizing
     * $id = $request->getQuery("id");
     *
     * // Returns value from $_GET["id"] with sanitizing
     * $id = $request->getQuery("id", "int");
     *
     * // Returns value from $_GET["id"] with a default value
     * $id = $request->getQuery("id", null, 150);
     *```
     *
     * @param string|null $name
     * @param mixed|null  $filters
     * @param mixed|null  $default
     * @param bool        $notAllowEmpty
     * @param bool        $noRecursive
     *
     * @return mixed
     */
    public function getQuery(string $name = null, $filters = null, $default = null, bool $notAllowEmpty = false, bool $noRecursive = false)
    {
        // TODO: Implement getQuery() method.
    }

    /**
     * Gets HTTP raw request body
     *
     * @return string
     */
    public function getRawBody(): string
    {
        // TODO: Implement getRawBody() method.
    }

    /**
     * Gets HTTP schema (http/https)
     *
     * @return string
     */
    public function getScheme(): string
    {
        // TODO: Implement getScheme() method.
    }

    /**
     * Gets variable from $_SERVER superglobal
     *
     * @param string $name
     *
     * @return string|null
     */
    public function getServer(string $name): ?string
    {
        // TODO: Implement getServer() method.
    }

    /**
     * Gets active server address IP
     *
     * @return string
     */
    public function getServerAddress(): string
    {
        // TODO: Implement getServerAddress() method.
    }

    /**
     * Gets active server name
     *
     * @return string
     */
    public function getServerName(): string
    {
        // TODO: Implement getServerName() method.
    }

    /**
     * Gets attached files as Phalcon\Http\Request\FileInterface compatible
     * instances
     *
     * @param bool $onlySuccessful
     * @param bool $namedKeys
     *
     * @return array
     */
    public function getUploadedFiles(bool $onlySuccessful = false, bool $namedKeys = false): array
    {
        // TODO: Implement getUploadedFiles() method.
    }

    /**
     * Gets HTTP user agent used to made the request
     *
     * @return string
     */
    public function getUserAgent(): string
    {
        // TODO: Implement getUserAgent() method.
    }

    /**
     * Checks whether $_REQUEST superglobal has certain index
     *
     * @param string $name
     *
     * @return bool
     */
    public function has(string $name): bool
    {
        // TODO: Implement has() method.
    }

    /**
     * Checks whether request include attached files
     *
     * @return bool
     */
    public function hasFiles(): bool
    {
        // TODO: Implement hasFiles() method.
    }

    /**
     * Checks whether headers has certain index
     *
     * @param string $header
     *
     * @return bool
     */
    public function hasHeader(string $header): bool
    {
        // TODO: Implement hasHeader() method.
    }

    /**
     * Checks whether $_GET superglobal has certain index
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasQuery(string $name): bool
    {
        // TODO: Implement hasQuery() method.
    }

    /**
     * Checks whether $_POST superglobal has certain index
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasPost(string $name): bool
    {
        // TODO: Implement hasPost() method.
    }

    /**
     * Checks whether the PUT data has certain index
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasPut(string $name): bool
    {
        // TODO: Implement hasPut() method.
    }

    /**
     * Checks whether $_SERVER superglobal has certain index
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasServer(string $name): bool
    {
        // TODO: Implement hasServer() method.
    }

    /**
     * Checks whether request has been made using ajax. Checks if
     * $_SERVER["HTTP_X_REQUESTED_WITH"] === "XMLHttpRequest"
     *
     * @return bool
     */
    public function isAjax(): bool
    {
        // TODO: Implement isAjax() method.
    }

    /**
     * Checks whether HTTP method is CONNECT. if
     * $_SERVER["REQUEST_METHOD"] === "CONNECT"
     *
     * @return bool
     */
    public function isConnect(): bool
    {
        // TODO: Implement isConnect() method.
    }

    /**
     * Checks whether HTTP method is DELETE. if
     * $_SERVER["REQUEST_METHOD"] === "DELETE"
     *
     * @return bool
     */
    public function isDelete(): bool
    {
        // TODO: Implement isDelete() method.
    }

    /**
     * Checks whether HTTP method is GET. if
     * $_SERVER["REQUEST_METHOD"] === "GET"
     *
     * @return bool
     */
    public function isGet(): bool
    {
        // TODO: Implement isGet() method.
    }

    /**
     * Checks whether HTTP method is HEAD. if
     * $_SERVER["REQUEST_METHOD"] === "HEAD"
     *
     * @return bool
     */
    public function isHead(): bool
    {
        // TODO: Implement isHead() method.
    }

    /**
     * Check if HTTP method match any of the passed methods
     *
     * @param string|array $methods
     * @param bool         $strict
     *
     * @return bool
     */
    public function isMethod($methods, bool $strict = false): bool
    {
        // TODO: Implement isMethod() method.
    }

    /**
     * Checks whether HTTP method is OPTIONS. if
     * $_SERVER["REQUEST_METHOD"] === "OPTIONS"
     *
     * @return bool
     */
    public function isOptions(): bool
    {
        // TODO: Implement isOptions() method.
    }

    /**
     * Checks whether HTTP method is POST. if
     * $_SERVER["REQUEST_METHOD"] === "POST"
     *
     * @return bool
     */
    public function isPost(): bool
    {
        // TODO: Implement isPost() method.
    }

    /**
     * Checks whether HTTP method is PURGE (Squid and Varnish support). if
     * $_SERVER["REQUEST_METHOD"] === "PURGE"
     *
     * @return bool
     */
    public function isPurge(): bool
    {
        // TODO: Implement isPurge() method.
    }

    /**
     * Checks whether HTTP method is PUT. if
     * $_SERVER["REQUEST_METHOD"] === "PUT"
     *
     * @return bool
     */
    public function isPut(): bool
    {
        // TODO: Implement isPut() method.
    }

    /**
     * Checks whether request has been made using any secure layer
     *
     * @return bool
     */
    public function isSecure(): bool
    {
        // TODO: Implement isSecure() method.
    }

    /**
     * Checks whether request has been made using SOAP
     *
     * @return bool
     */
    public function isSoap(): bool
    {
        // TODO: Implement isSoap() method.
    }

    /**
     * Checks whether HTTP method is TRACE.
     * if $_SERVER["REQUEST_METHOD"] === "TRACE"
     *
     * @return bool
     */
    public function isTrace(): bool
    {
        // TODO: Implement isTrace() method.
    }

    /**
     * Returns the number of files available
     *
     * @param bool $onlySuccessful
     *
     * @return int
     */
    public function numFiles(bool $onlySuccessful = false): int
    {
        // TODO: Implement numFiles() method.
    }
}
