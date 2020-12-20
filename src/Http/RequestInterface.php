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

namespace Phalcon\Http;


/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace Phalcon\Http;

use Phalcon\Http\Request\FileInterface;
use stdClass;

/**
 * Interface for Phalcon\Http\Request
 */
interface RequestInterface
{
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
    );

    /**
     * Gets an array with mime/types and their quality accepted by the
     * browser/client from _SERVER["HTTP_ACCEPT"]
     *
     * @return array
     */
    public function getAcceptableContent(): array;

    /**
     * Gets auth info accepted by the browser/client from
     * $_SERVER["PHP_AUTH_USER"]
     *
     * @return array|null
     */
    public function getBasicAuth(): ?array;

    /**
     * Gets best mime/type accepted by the browser/client from
     * _SERVER["HTTP_ACCEPT"]
     *
     * @return string
     */
    public function getBestAccept(): string;

    /**
     * Gets best charset accepted by the browser/client from
     * _SERVER["HTTP_ACCEPT_CHARSET"]
     *
     * @return string
     */
    public function getBestCharset(): string;

    /**
     * Gets best language accepted by the browser/client from
     * _SERVER["HTTP_ACCEPT_LANGUAGE"]
     *
     * @return string
     */
    public function getBestLanguage(): string;

    /**
     * Gets most possible client IPv4 Address. This method searches in
     * $_SERVER["REMOTE_ADDR"] and optionally in
     * $_SERVER["HTTP_X_FORWARDED_FOR"]
     *
     * @param bool $trustForwardedHeader
     *
     * @return string|bool
     */
    public function getClientAddress(bool $trustForwardedHeader = false);

    /**
     * Gets a charsets array and their quality accepted by the browser/client
     * from _SERVER["HTTP_ACCEPT_CHARSET"]
     *
     * @return array
     */
    public function getClientCharsets(): array;

    /**
     * Gets content type which request has been made
     *
     * @return string|null
     */
    public function getContentType(): ?string;

    /**
     * Gets auth info accepted by the browser/client from
     * $_SERVER["PHP_AUTH_DIGEST"]
     *
     * @return array
     */
    public function getDigestAuth(): array;

    /**
     * Gets HTTP header from request data
     *
     * @param string $header
     *
     * @return string
     */
    public function getHeader(string $header): string;

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
    public function getHeaders(): array;

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
    public function getHttpHost(): string;

    /**
     * Gets web page that refers active request. ie: http://www.google.com
     *
     * @return string
     */
    public function getHTTPReferer(): string;

    /**
     * Gets decoded JSON HTTP raw request body
     *
     * @param bool $associative
     *
     * @return stdClass|array|bool
     */
    public function getJsonRawBody(bool $associative = false);

    /**
     * Gets languages array and their quality accepted by the browser/client
     * from _SERVER["HTTP_ACCEPT_LANGUAGE"]
     *
     * @return array
     */
    public function getLanguages(): array;

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
    public function getMethod(): string;

    /**
     * Gets information about the port on which the request is made
     *
     * @return int
     */
    public function getPort(): int;

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
    public function getURI(bool $onlyPath = false): string;

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
    );

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
    public function getPut(
        string $name = null,
        $filters = null,
        $default = null,
        bool $notAllowEmpty = false,
        bool $noRecursive = false
    );

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
    public function getQuery(
        string $name = null,
        $filters = null,
        $default = null,
        bool $notAllowEmpty = false,
        bool $noRecursive = false
    );

    /**
     * Gets HTTP raw request body
     *
     * @return string
     */
    public function getRawBody(): string;

    /**
     * Gets HTTP schema (http/https)
     *
     * @return string
     */
    public function getScheme(): string;

    /**
     * Gets variable from $_SERVER superglobal
     *
     * @param string $name
     *
     * @return string|null
     */
    public function getServer(string $name): ?string;

    /**
     * Gets active server address IP
     *
     * @return string
     */
    public function getServerAddress(): string;

    /**
     * Gets active server name
     *
     * @return string
     */
    public function getServerName(): string;

    /**
     * Gets attached files as Phalcon\Http\Request\FileInterface compatible
     * instances
     *
     * @param bool $onlySuccessful
     * @param bool $namedKeys
     *
     * @return array
     */
    public function getUploadedFiles(
        bool $onlySuccessful = false,
        bool $namedKeys = false
    ): array;

    /**
     * Gets HTTP user agent used to made the request
     *
     * @return string
     */
    public function getUserAgent(): string;

    /**
     * Checks whether $_REQUEST superglobal has certain index
     *
     * @param string $name
     *
     * @return bool
     */
    public function has(string $name): bool;

    /**
     * Checks whether request include attached files
     *
     * @return bool
     */
    public function hasFiles(): bool;

    /**
     * Checks whether headers has certain index
     *
     * @param string $header
     *
     * @return bool
     */
    public function hasHeader(string $header): bool;

    /**
     * Checks whether $_GET superglobal has certain index
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasQuery(string $name): bool;

    /**
     * Checks whether $_POST superglobal has certain index
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasPost(string $name): bool;

    /**
     * Checks whether the PUT data has certain index
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasPut(string $name): bool;

    /**
     * Checks whether $_SERVER superglobal has certain index
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasServer(string $name): bool;

    /**
     * Checks whether request has been made using ajax. Checks if
     * $_SERVER["HTTP_X_REQUESTED_WITH"] === "XMLHttpRequest"
     *
     * @return bool
     */
    public function isAjax(): bool;

    /**
     * Checks whether HTTP method is CONNECT. if
     * $_SERVER["REQUEST_METHOD"] === "CONNECT"
     *
     * @return bool
     */
    public function isConnect(): bool;

    /**
     * Checks whether HTTP method is DELETE. if
     * $_SERVER["REQUEST_METHOD"] === "DELETE"
     *
     * @return bool
     */
    public function isDelete(): bool;

    /**
     * Checks whether HTTP method is GET. if
     * $_SERVER["REQUEST_METHOD"] === "GET"
     *
     * @return bool
     */
    public function isGet(): bool;

    /**
     * Checks whether HTTP method is HEAD. if
     * $_SERVER["REQUEST_METHOD"] === "HEAD"
     *
     * @return bool
     */
    public function isHead(): bool;

    /**
     * Check if HTTP method match any of the passed methods
     *
     * @param string|array $methods
     * @param bool         $strict
     *
     * @return bool
     */
    public function isMethod($methods, bool $strict = false): bool;

    /**
     * Checks whether HTTP method is OPTIONS. if
     * $_SERVER["REQUEST_METHOD"] === "OPTIONS"
     *
     * @return bool
     */
    public function isOptions(): bool;

    /**
     * Checks whether HTTP method is POST. if
     * $_SERVER["REQUEST_METHOD"] === "POST"
     *
     * @return bool
     */
    public function isPost(): bool;

    /**
     * Checks whether HTTP method is PURGE (Squid and Varnish support). if
     * $_SERVER["REQUEST_METHOD"] === "PURGE"
     *
     * @return bool
     */
    public function isPurge(): bool;

    /**
     * Checks whether HTTP method is PUT. if
     * $_SERVER["REQUEST_METHOD"] === "PUT"
     *
     * @return bool
     */
    public function isPut(): bool;

    /**
     * Checks whether request has been made using any secure layer
     *
     * @return bool
     */
    public function isSecure(): bool;

    /**
     * Checks whether request has been made using SOAP
     *
     * @return bool
     */
    public function isSoap(): bool;

    /**
     * Checks whether HTTP method is TRACE.
     * if $_SERVER["REQUEST_METHOD"] === "TRACE"
     *
     * @return bool
     */
    public function isTrace(): bool;

    /**
     * Returns the number of files available
     *
     * @param bool $onlySuccessful
     *
     * @return int
     */
    public function numFiles(bool $onlySuccessful = false): int;
}
