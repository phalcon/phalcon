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

use Phalcon\Di\AbstractInjectionAware;
use Phalcon\Events\EventsAwareInterface;
use Phalcon\Events\Exception as EventsException;
use Phalcon\Events\Traits\EventsAwareTrait;
use Phalcon\Filter\FilterInterface;
use Phalcon\Http\Message\Interfaces\RequestMethodInterface;
use Phalcon\Http\Request\Exception;
use Phalcon\Http\Request\File;
use Phalcon\Http\Request\FileInterface;
use stdClass;
use UnexpectedValueException;

use function array_key_exists;
use function array_merge;
use function base64_decode;
use function base64_encode;
use function explode;
use function file_get_contents;
use function gethostbyname;
use function is_array;
use function is_numeric;
use function is_string;
use function json_decode;
use function parse_str;
use function preg_match_all;
use function preg_replace;
use function preg_split;
use function str_replace;
use function stripos;
use function strrpos;
use function strtolower;
use function strtoupper;
use function substr;
use function trim;
use function ucwords;

use const PREG_SPLIT_NO_EMPTY;
use const UPLOAD_ERR_OK;

/**
 * Encapsulates request information for easy and secure access from application
 * controllers.
 *
 * The request object is a simple value object that is passed between the
 * dispatcher and controller classes. It packages the HTTP request environment.
 *
 *```php
 * use Phalcon\Http\Request;
 *
 * $request = new Request();
 *
 * if ($request->isPost() && $request->isAjax()) {
 *     echo "Request was made using POST and AJAX";
 * }
 *
 * // Retrieve SERVER variables
 * $request->getServer("HTTP_HOST");
 *
 * // GET, POST, PUT, DELETE, HEAD, OPTIONS, PATCH, PURGE, TRACE, CONNECT
 * $request->getMethod();
 *
 * // An array of languages the client accepts
 * $request->getLanguages();
 *```
 */
class Request extends AbstractInjectionAware implements
    EventsAwareInterface,
    RequestInterface,
    RequestMethodInterface
{
    use EventsAwareTrait;

    /**
     * @var FilterInterface|null
     */
    private FilterInterface | null $filterService = null;

    /**
     * @var bool
     */
    private bool $methodOverride = false;
    /**
     * @var array|null
     */
    private array | null $postCache = null;
    /**
     * @var array
     */
    private array $queryFilters = [];
    /**
     * @var string
     */
    private string $rawBody = '';
    /**
     * @var bool
     */
    private bool $strictHostCheck = false;
    /**
     * @var array
     */
    protected array $trustedProxies = [];

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
     * @param mixed|null  $defaultValue
     * @param bool        $notAllowEmpty
     * @param bool        $noRecursive
     *
     * @return mixed
     * @todo check the filters
     */
    public function get(
        string | null $name = null,
        mixed $filters = null,
        mixed $defaultValue = null,
        bool $notAllowEmpty = false,
        bool $noRecursive = false
    ): mixed {
        return $this->getHelper(
            $_REQUEST,
            $name,
            $filters,
            $defaultValue,
            $notAllowEmpty,
            $noRecursive
        );
    }

    /**
     * Gets an array with mime/types and their quality accepted by the
     * browser/client from _SERVER["HTTP_ACCEPT"]
     *
     * @return array
     */
    public function getAcceptableContent(): array
    {
        return $this->getQualityHeader("HTTP_ACCEPT", "accept");
    }

    /**
     * Gets auth info accepted by the browser/client from
     * $_SERVER["PHP_AUTH_USER"]
     *
     * @return string[]|null
     */
    public function getBasicAuth(): array | null
    {
        if (true !== $this->hasServer('PHP_AUTH_USER')) {
            return null;
        }

        return [
            'username' => $this->getServer('PHP_AUTH_USER'),
            'password' => $this->getServer('PHP_AUTH_PW'),
        ];
    }

    /**
     * Gets best mime/type accepted by the browser/client from
     * _SERVER["HTTP_ACCEPT"]
     *
     * @return string
     */
    public function getBestAccept(): string
    {
        return $this->getBestQuality($this->getAcceptableContent(), "accept");
    }

    /**
     * Gets best charset accepted by the browser/client from
     * _SERVER["HTTP_ACCEPT_CHARSET"]
     *
     * @return string
     */
    public function getBestCharset(): string
    {
        return $this->getBestQuality($this->getClientCharsets(), "charset");
    }

    /**
     * Gets the best language accepted by the browser/client from
     * _SERVER["HTTP_ACCEPT_LANGUAGE"]
     *
     * @return string
     */
    public function getBestLanguage(): string
    {
        return $this->getBestQuality($this->getLanguages(), 'language');
    }

    /**
     * Gets most possible client IP Address. This method searches in
     * `$_SERVER["REMOTE_ADDR"]` and optionally in
     * `$_SERVER["HTTP_X_FORWARDED_FOR"]` and returns the first non-private or non-reserved IP address
     *
     * @param bool $trustForwardedHeader
     *
     * @return string|false
     * @throws \Exception
     */
    public function getClientAddress(bool $trustForwardedHeader = false): string | bool
    {
        $address = null;

        if ($trustForwardedHeader) {
            $address = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? null;
            if (null === $address) {
                $address = $_SERVER['HTTP_CLIENT_IP'] ?? null;
            }

            if (null !== $address && strpos($address, ',') !== false) {
                /**
                 * The client address has multiples parts,
                 * only return the first non-private/non-reserved IP
                 */
                $trustedProxies = $this->trustedProxies;
                $forwardedIps = explode(',', $address);
                if (!empty($trustedProxies)) {
                    // verify if we trust the forwarded proxy
                    $isTrusted = false;
                    $reverseForwardedIps = array_reverse($forwardedIps);
                    foreach ($reverseForwardedIps as $invertForwardedIp) {
                        if ($isTrusted === true) {
                            break;
                        }
                        foreach ($trustedProxies as $trustedForwardedIp) {
                            if (strpos($trustedForwardedIp, "/") !== false) {
                                $isIpAddressInCIDR = $this->isIpAddressInCIDR($invertForwardedIp, $trustedForwardedIp);
                                if ($isIpAddressInCIDR === true) {
                                    $isTrusted = true;
                                    break;
                                }
                            } else {
                                if ($invertForwardedIp === $trustedForwardedIp) {
                                    $isTrusted = true;
                                    break;
                                }
                            }
                        }
                    }

                    if (!$isTrusted) {
                        throw new \Exception("The forwarded proxy IP addresses are not trusted.");
                    }
                }

                // retrieve the first valid IP that is not reserved or private
                $filterService = $this->getFilterService();
                foreach ($forwardedIps as $forwardedIp) {
                    $filtered = $filterService->sanitize($forwardedIp, [
                        "ip" => [
                            "filter" => FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
                        ]
                    ]);
                    if ($filtered) {
                        return $filtered;
                    }
                }

                return false;
            }
        } else {
            $address = $_SERVER["REMOTE_ADDR"];
        }

        if (!is_string($address)) {
            return false;
        }

        return $address;
    }

    /**
     * Gets a charsets array and their quality accepted by the browser/client
     * from _SERVER["HTTP_ACCEPT_CHARSET"]
     *
     * @return array
     */
    public function getClientCharsets(): array
    {
        return $this->getQualityHeader('HTTP_ACCEPT_CHARSET', 'charset');
    }

    /**
     * Gets content type which request has been made
     *
     * @return string|null
     */
    public function getContentType(): string | null
    {
        return $_SERVER['CONTENT_TYPE'] ?? null;
    }

    /**
     * Gets auth info accepted by the browser/client from
     * $_SERVER["PHP_AUTH_DIGEST"]
     *
     * @return array
     */
    public function getDigestAuth(): array
    {
        $auth = [];
        if (isset($_SERVER['PHP_AUTH_DIGEST'])) {
            $matches = [];
            $digest  = $_SERVER['PHP_AUTH_DIGEST'];

            if (
                !preg_match_all(
                    "#(\\w+)=(['\"]?)([^'\" ,]+)\\2#",
                    $digest,
                    $matches,
                    2
                )
            ) {
                return $auth;
            }

            if (!empty($matches)) {
                foreach ($matches as $match) {
                    $auth[$match[1]] = $match[3];
                }
            }
        }

        return $auth;
    }

    /**
     * Gets filtered data
     *
     * @param string      $methodKey
     * @param string      $method
     * @param string|null $name
     * @param mixed|null  $defaultValue
     * @param bool        $notAllowEmpty
     * @param bool        $noRecursive
     *
     * @return mixed
     */
    public function getFilteredData(
        string $methodKey,
        string $method,
        string | null $name = null,
        mixed $defaultValue = null,
        bool $notAllowEmpty = false,
        bool $noRecursive = false
    ): mixed {
        $filters = $this->queryFilters[$methodKey][$name] ?? [];

        return $this->{$method}(
            $name,
            $filters,
            $defaultValue,
            $notAllowEmpty,
            $noRecursive
        );
    }

    /**
     * Retrieves a patch value always sanitized with the preset filters
     *
     * @param string|null $name
     * @param mixed|null  $defaultValue
     * @param bool        $notAllowEmpty
     * @param bool        $noRecursive
     *
     * @return mixed
     */
    public function getFilteredPatch(
        string | null $name = null,
        mixed $defaultValue = null,
        bool $notAllowEmpty = false,
        bool $noRecursive = false
    ): mixed {
        return $this->getFilteredData(
            self::METHOD_PATCH,
            'getPatch',
            $name,
            $defaultValue,
            $notAllowEmpty,
            $noRecursive
        );
    }

    /**
     * Retrieves a post value always sanitized with the preset filters
     *
     * @param string|null $name
     * @param mixed|null  $defaultValue
     * @param bool        $notAllowEmpty
     * @param bool        $noRecursive
     *
     * @return mixed
     */
    public function getFilteredPost(
        string | null $name = null,
        mixed $defaultValue = null,
        bool $notAllowEmpty = false,
        bool $noRecursive = false
    ): mixed {
        return $this->getFilteredData(
            self::METHOD_POST,
            'getPost',
            $name,
            $defaultValue,
            $notAllowEmpty,
            $noRecursive
        );
    }

    /**
     * Retrieves a put value always sanitized with the preset filters
     *
     * @param string|null $name
     * @param mixed|null  $defaultValue
     * @param bool        $notAllowEmpty
     * @param bool        $noRecursive
     *
     * @return mixed
     */
    public function getFilteredPut(
        string | null $name = null,
        mixed $defaultValue = null,
        bool $notAllowEmpty = false,
        bool $noRecursive = false
    ): mixed {
        return $this->getFilteredData(
            self::METHOD_PUT,
            'getPut',
            $name,
            $defaultValue,
            $notAllowEmpty,
            $noRecursive
        );
    }

    /**
     * Retrieves a query/get value always sanitized with the preset filters
     *
     * @param string|null $name
     * @param mixed|null  $defaultValue
     * @param bool        $notAllowEmpty
     * @param bool        $noRecursive
     *
     * @return mixed
     */
    public function getFilteredQuery(
        string | null $name = null,
        mixed $defaultValue = null,
        bool $notAllowEmpty = false,
        bool $noRecursive = false
    ): mixed {
        return $this->getFilteredData(
            self::METHOD_GET,
            'getQuery',
            $name,
            $defaultValue,
            $notAllowEmpty,
            $noRecursive
        );
    }

    /**
     * Gets web page that refers active request. ie: https://www.google.com
     *
     * @return string
     */
    public function getHTTPReferer(): string
    {
        return $_SERVER['HTTP_REFERER'] ?? '';
    }

    /**
     * Gets HTTP header from request data
     *
     * @param string $header
     *
     * @return string
     */
    final public function getHeader(string $header): string
    {
        $name = strtoupper(strtr($header, '-', '_'));

        if (isset($_SERVER[$name])) {
            return $_SERVER[$name];
        }

        if (isset($_SERVER['HTTP_' . $name])) {
            return $_SERVER['HTTP_' . $name];
        }

        return '';
    }

    /**
     * Returns the available headers in the request
     *
     * <code>
     * $_SERVER = [
     *     "PHP_AUTH_USER" => "phalcon",
     *     "PHP_AUTH_PW"   => "secret",
     * ];
     *
     * $headers = $request->getHeaders();
     *
     * echo $headers["Authorization"]; // Basic cGhhbGNvbjpzZWNyZXQ=
     * </code>
     *
     * @return array
     * @throws EventsException
     */
    public function getHeaders(): array
    {
        $headers        = [];
        $contentHeaders = [
            'CONTENT_TYPE'   => true,
            'CONTENT_LENGTH' => true,
            'CONTENT_MD5'    => true,
        ];

        foreach ($_SERVER as $name => $value) {
            // Note: The starts_with uses case-insensitive search here
            if (str_starts_with(strtoupper($name), 'HTTP_')) {
                $name = ucwords(
                    strtolower(
                        str_replace(
                            '_',
                            ' ',
                            substr($name, 5)
                        )
                    )
                );

                $name = str_replace(' ', '-', $name);

                $headers[$name] = $value;

                continue;
            }

            // The "CONTENT_" headers are not prefixed with "HTTP_".
            $name = strtoupper($name);

            if (isset($contentHeaders[$name])) {
                $name = ucwords(
                    strtolower(
                        str_replace('_', ' ', $name)
                    )
                );

                $name = str_replace(' ', '-', $name);

                $headers[$name] = $value;
            }
        }

        $authHeaders = $this->resolveAuthorizationHeaders();

        // Protect for future (child classes) changes
        return array_merge($headers, $authHeaders);
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
     */
    public function getHttpHost(): string
    {
        /**
         * Get the server name from $_SERVER["HTTP_HOST"]
         */
        $host = $this->getServer('HTTP_HOST');

        if (empty($host)) {
            /**
             * Get the server name from $_SERVER["SERVER_NAME"]
             */
            $host = $this->getServer('SERVER_NAME');
            if (empty($host)) {
                /**
                 * Get the server address from $_SERVER["SERVER_ADDR"]
                 */
                $host = $this->getServer('SERVER_ADDR');
            }
        }

        $cleanHost = $host;
        if (!empty($host) && $this->strictHostCheck) {
            /**
             * Cleanup. Force lowercase as per RFC 952/2181
             */
            $cleanHost = strtolower(trim($host));

            if (str_contains($cleanHost, ':')) {
                $cleanHost = preg_replace(
                    "/:[[:digit:]]+$/",
                    '',
                    $cleanHost
                );
            }

            /**
             * Host may contain only the ASCII letters 'a' through 'z'
             * (in a case-insensitive manner), the digits '0' through '9', and
             * the hyphen ('-') as per RFC 952/2181
             */
            if (
                '' !== preg_replace("/[a-z0-9-]+\.?/", "", $cleanHost)
            ) {
                throw new UnexpectedValueException('Invalid host ' . $host);
            }
        }

        return (string)$cleanHost;
    }

    /**
     * Return the HTTP method parameter override flag
     *
     * @return bool
     */
    public function getHttpMethodParameterOverride(): bool
    {
        return $this->methodOverride;
    }

    /**
     * Gets decoded JSON HTTP raw request body
     *
     * @param bool $associative
     *
     * @return array|bool|stdClass
     */
    public function getJsonRawBody(bool $associative = false): array | bool | stdClass
    {
        $rawBody = $this->getRawBody();

        return json_decode($rawBody, $associative);
    }

    /**
     * Gets languages array and their quality accepted by the browser/client
     * from _SERVER["HTTP_ACCEPT_LANGUAGE"]
     *
     * @return array
     */
    public function getLanguages(): array
    {
        return $this->getQualityHeader('HTTP_ACCEPT_LANGUAGE', 'language');
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
    final public function getMethod(): string
    {
        if (!isset($_SERVER['REQUEST_METHOD'])) {
            return self::METHOD_GET;
        }

        $returnMethod = strtoupper($_SERVER['REQUEST_METHOD']);

        if (self::METHOD_POST === $returnMethod) {
            $overriddenMethod = $this->getHeader('X-HTTP-METHOD-OVERRIDE');

            if (!empty($overriddenMethod)) {
                $returnMethod = strtoupper($overriddenMethod);
            } elseif (
                true === $this->methodOverride &&
                isset($_REQUEST['_method'])
            ) {
                $returnMethod = strtoupper($_REQUEST['_method']);
            }
        }

        if (true !== $this->isValidHttpMethod($returnMethod)) {
            return self::METHOD_GET;
        }

        return $returnMethod;
    }

    /**
     * Gets a variable from put request
     *
     *```php
     * // Returns value from $_PATCH["user_email"] without sanitizing
     * $userEmail = $request->getPatch("user_email");
     *
     * // Returns value from $_PATCH["user_email"] with sanitizing
     * $userEmail = $request->getPatch("user_email", "email");
     *```
     *
     * @param string|null $name
     * @param mixed|null  $filters
     * @param mixed|null  $defaultValue
     * @param bool        $notAllowEmpty
     * @param bool        $noRecursive
     *
     * @return mixed
     */
    public function getPatch(
        string | null $name = null,
        mixed $filters = null,
        mixed $defaultValue = null,
        bool $notAllowEmpty = false,
        bool $noRecursive = false
    ): mixed {
        $this->postCache = $this->getPostData($_POST);

        return $this->getHelper(
            $this->postCache,
            $name,
            $filters,
            $defaultValue,
            $notAllowEmpty,
            $noRecursive
        );
    }

    /**
     * Gets information about the port on which the request is made.
     *
     * @return int
     */
    public function getPort(): int
    {
        /**
         * Get the server name from $_SERVER["HTTP_HOST"]
         */
        $host = $this->getServer('HTTP_HOST');
        if (empty($host)) {
            return (int)$this->getServer('SERVER_PORT');
        }

        if (str_contains($host, ':')) {
            $pos = strrpos($host, ":");

            if (false !== $pos) {
                return (int)substr($host, $pos + 1);
            }
        }

        return 'https' === $this->getScheme() ? 443 : 80;
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
     * @param mixed|null  $defaultValue
     * @param bool        $notAllowEmpty
     * @param bool        $noRecursive
     *
     * @return mixed
     */
    public function getPost(
        string | null $name = null,
        mixed $filters = null,
        mixed $defaultValue = null,
        bool $notAllowEmpty = false,
        bool $noRecursive = false
    ): mixed {
        $this->postCache = $this->getPostData($_POST);

        return $this->getHelper(
            $this->postCache,
            $name,
            $filters,
            $defaultValue,
            $notAllowEmpty,
            $noRecursive
        );
    }

    /**
     * Gets the preferred ISO locale variant.
     *
     * Gets the preferred locale accepted by the client from the
     * "Accept-Language" request HTTP header and returns the
     * base part of it i.e. `en` instead of `en-US`.
     *
     * Note: This method relies on the `$_SERVER["HTTP_ACCEPT_LANGUAGE"]`
     * header.
     *
     * @link https://www.iso.org/standard/50707.html
     *
     * @return string
     */
    public function getPreferredIsoLocaleVariant(): string
    {
        $language = $this->getBestLanguage();
        $language = explode('-', $language);

        return '*' !== $language[0] ? $language[0] : '';
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
     * @param mixed|null  $defaultValue
     * @param bool        $notAllowEmpty
     * @param bool        $noRecursive
     *
     * @return mixed
     */
    public function getPut(
        string | null $name = null,
        mixed $filters = null,
        mixed $defaultValue = null,
        bool $notAllowEmpty = false,
        bool $noRecursive = false
    ): mixed {
        $this->postCache = $this->getPostData($this->postCache);

        return $this->getHelper(
            $this->postCache,
            $name,
            $filters,
            $defaultValue,
            $notAllowEmpty,
            $noRecursive
        );
    }

    /**
     * Gets variable from $_GET superglobal applying filters if needed.
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
     * @param mixed|null  $defaultValue
     * @param bool        $notAllowEmpty
     * @param bool        $noRecursive
     *
     * @return mixed
     */
    public function getQuery(
        string | null $name = null,
        mixed $filters = null,
        mixed $defaultValue = null,
        bool $notAllowEmpty = false,
        bool $noRecursive = false
    ): mixed {
        return $this->getHelper(
            $_GET,
            $name,
            $filters,
            $defaultValue,
            $notAllowEmpty,
            $noRecursive
        );
    }

    /**
     * Gets HTTP raw request body
     *
     * @return string
     */
    public function getRawBody(): string
    {
        if (empty($this->rawBody)) {
            /**
             * We need store the read raw body because it can't be read again
             */
            $this->rawBody = file_get_contents('php://input');
        }

        return $this->rawBody;
    }

    /**
     * Gets HTTP schema (http/https)
     *
     * @return string
     */
    public function getScheme(): string
    {
        $https = $this->getServer('HTTPS');

        return (!empty($https) && 'off' !== $https) ? 'https' : 'http';
    }

    /**
     * Gets variable from $_SERVER superglobal
     *
     * @param string $name
     *
     * @return string|null
     */
    public function getServer(string $name): string | null
    {
        return $_SERVER[$name] ?? null;
    }

    /**
     * Gets active server address IP
     *
     * @return string
     */
    public function getServerAddress(): string
    {
        $serverAddr = $this->getServer('SERVER_ADDR');

        return $serverAddr ?: gethostbyname('localhost');
    }

    /**
     * Gets active server name
     *
     * @return string
     */
    public function getServerName(): string
    {
        $serverName = $this->getServer("SERVER_NAME");

        return $serverName ?: 'localhost';
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
    final public function getURI(bool $onlyPath = false): string
    {
        $requestURI = $this->getServer("REQUEST_URI");
        if (null === $requestURI) {
            return '';
        }

        if (true === $onlyPath) {
            $requestURI = explode('?', $requestURI)[0];
        }

        return $requestURI;
    }

    /**
     * Gets attached files as Phalcon\Http\Request\File instances
     *
     * @param bool $onlySuccessful
     * @param bool $namedKeys
     *
     * @return FileInterface[]
     */
    public function getUploadedFiles(
        bool $onlySuccessful = false,
        bool $namedKeys = false
    ): array {
        $files      = [];
        $superFiles = $_FILES;

        if (!empty($superFiles)) {
            foreach ($superFiles as $prefix => $input) {
                if (is_array($input['name'])) {
                    $smoothInput = $this->smoothFiles(
                        $input["name"],
                        $input["type"],
                        $input["tmp_name"],
                        $input["size"],
                        $input["error"],
                        $prefix
                    );

                    foreach ($smoothInput as $file) {
                        if (
                            false === $onlySuccessful ||
                            UPLOAD_ERR_OK === $file['error']
                        ) {
                            $dataFile = [
                                'name'     => $file["name"],
                                'type'     => $file["type"],
                                'tmp_name' => $file["tmp_name"],
                                'size'     => $file["size"],
                                'error'    => $file["error"],
                            ];

                            $files = $this->processFiles(
                                $files,
                                $namedKeys,
                                $dataFile,
                                $file['key']
                            );
                        }
                    }
                } else {
                    if (
                        false === $onlySuccessful ||
                        UPLOAD_ERR_OK === $input['error']
                    ) {
                        $files = $this->processFiles(
                            $files,
                            $namedKeys,
                            $input,
                            $prefix
                        );
                    }
                }
            }
        }

        return $files;
    }

    /**
     * Gets HTTP user agent used to make the request
     *
     * @return string
     */
    public function getUserAgent(): string
    {
        return (string)$this->getServer("HTTP_USER_AGENT");
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
        return array_key_exists($name, $_REQUEST);
    }

    /**
     * Returns if the request has files or not
     *
     * @return bool
     */
    public function hasFiles(): bool
    {
        return $this->numFiles(true) > 0;
    }

    /**
     * Checks whether headers has certain index
     *
     * @param string $header
     *
     * @return bool
     */
    final public function hasHeader(string $header): bool
    {
        $name = strtoupper(strtr($header, '-', '_'));

        return $this->hasServer($name) || $this->hasServer('HTTP_' . $name);
    }

    /**
     * Checks whether the PATCH data has certain index
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasPatch(string $name): bool
    {
        return array_key_exists($name, $this->getPatch());
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
        return array_key_exists($name, $this->getPost());
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
        return array_key_exists($name, $this->getPut());
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
        return array_key_exists($name, $_GET);
    }

    /**
     * Checks whether $_SERVER superglobal has certain index
     *
     * @param string $name
     *
     * @return bool
     */
    final public function hasServer(string $name): bool
    {
        return array_key_exists($name, $_SERVER);
    }

    /**
     * Checks whether request has been made using ajax
     *
     * @return bool
     */
    public function isAjax(): bool
    {
        return true === $this->hasServer("HTTP_X_REQUESTED_WITH") &&
            'XMLHttpRequest' === $this->getServer("HTTP_X_REQUESTED_WITH");
    }

    /**
     * Checks whether request content type contains json data
     *
     * @return bool
     */
    public function isJson(): bool
    {
        return $this->hasServer("CONTENT_TYPE") &&
            stripos($this->getServer("CONTENT_TYPE"), "json") !== false;
    }

    /**
     * Checks whether HTTP method is CONNECT.
     * if _SERVER["REQUEST_METHOD"]==="CONNECT"
     *
     * @return bool
     */
    public function isConnect(): bool
    {
        return $this->getMethod() === self::METHOD_CONNECT;
    }

    /**
     * Checks whether HTTP method is DELETE.
     * if _SERVER["REQUEST_METHOD"]==="DELETE"
     *
     * @return bool
     */
    public function isDelete(): bool
    {
        return $this->getMethod() === self::METHOD_DELETE;
    }

    /**
     * Checks whether HTTP method is GET.
     * if _SERVER["REQUEST_METHOD"]==="GET"
     *
     * @return bool
     */
    public function isGet(): bool
    {
        return $this->getMethod() === self::METHOD_GET;
    }

    /**
     * Checks whether HTTP method is HEAD.
     * if _SERVER["REQUEST_METHOD"]==="HEAD"
     *
     * @return bool
     */
    public function isHead(): bool
    {
        return $this->getMethod() === self::METHOD_HEAD;
    }

    /**
     * Check if HTTP method match any of the passed methods
     * When strict is true it checks if validated methods are real HTTP methods
     *
     * @param array|string $methods
     * @param bool         $strict
     *
     * @return bool
     * @throws Exception
     * @todo check the $methods type - refactor this !!
     */
    public function isMethod(mixed $methods, bool $strict = false): bool
    {
        $httpMethod = $this->getMethod();

        if (is_string($methods)) {
            if (
                true === $strict &&
                true !== $this->isValidHttpMethod($methods)
            ) {
                throw new Exception('Invalid HTTP method: ' . $methods);
            }

            return $methods === $httpMethod;
        }

        if (is_array($methods)) {
            foreach ($methods as $method) {
                if (true === $this->isMethod($method, $strict)) {
                    return true;
                }
            }

            return false;
        }

        if (true === $strict) {
            throw new Exception('Invalid HTTP method: non-string');
        }

        return false;
    }

    /**
     * Checks whether HTTP method is OPTIONS.
     * if _SERVER["REQUEST_METHOD"]==="OPTIONS"
     *
     * @return bool
     */
    public function isOptions(): bool
    {
        return $this->getMethod() === self::METHOD_OPTIONS;
    }

    /**
     * Checks whether HTTP method is PATCH.
     * if _SERVER["REQUEST_METHOD"]==="PATCH"
     *
     * @return bool
     */
    public function isPatch(): bool
    {
        return $this->getMethod() === self::METHOD_PATCH;
    }

    /**
     * Checks whether HTTP method is POST.
     * if _SERVER["REQUEST_METHOD"]==="POST"
     *
     * @return bool
     */
    public function isPost(): bool
    {
        return $this->getMethod() === self::METHOD_POST;
    }

    /**
     * Checks whether HTTP method is PURGE (Squid and Varnish support).
     * if _SERVER["REQUEST_METHOD"]==="PURGE"
     *
     * @return bool
     */
    public function isPurge(): bool
    {
        return $this->getMethod() === self::METHOD_PURGE;
    }

    /**
     * Checks whether HTTP method is PUT.
     * if _SERVER["REQUEST_METHOD"]==="PUT"
     *
     * @return bool
     */
    public function isPut(): bool
    {
        return $this->getMethod() === self::METHOD_PUT;
    }

    /**
     * Checks whether request has been made using any secure layer
     *
     * @return bool
     */
    public function isSecure(): bool
    {
        return $this->getScheme() === "https";
    }

    /**
     * Checks whether request has been made using SOAP
     *
     * @return bool
     */
    public function isSoap(): bool
    {
        if (true === $this->hasServer('HTTP_SOAPACTION')) {
            return true;
        }

        $contentType = $this->getContentType();

        if (empty($contentType)) {
            return false;
        }

        return str_contains($contentType, 'application/soap+xml');
    }

    /**
     * Checks if the `Request::getHttpHost` method will be use strict validation
     * of host name or not
     *
     * @return bool
     */
    public function isStrictHostCheck(): bool
    {
        return $this->strictHostCheck;
    }

    /**
     * Checks whether HTTP method is TRACE.
     * if _SERVER["REQUEST_METHOD"]==="TRACE"
     *
     * @return bool
     */
    public function isTrace(): bool
    {
        return $this->getMethod() === self::METHOD_TRACE;
    }

    /**
     * Checks if a method is a valid HTTP method
     *
     * @param string $method
     *
     * @return bool
     */
    public function isValidHttpMethod(string $method): bool
    {
        return match (strtoupper($method)) {
            self::METHOD_CONNECT,
            self::METHOD_DELETE,
            self::METHOD_GET,
            self::METHOD_HEAD,
            self::METHOD_OPTIONS,
            self::METHOD_PATCH,
            self::METHOD_POST,
            self::METHOD_PURGE,
            self::METHOD_PUT,
            self::METHOD_TRACE => true,
            default            => false,
        };
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
        $numberFiles = 0;
        $files       = $_FILES;

        if (empty($files)) {
            return 0;
        }

        foreach ($files as $file) {
            if (isset($file['error'])) {
                $error = $file['error'];

                if (
                    !is_array($error) &&
                    (!$error || !$onlySuccessful)
                ) {
                    $numberFiles++;
                }

                if (is_array($error)) {
                    $numberFiles += $this->hasFileHelper(
                        $error,
                        $onlySuccessful
                    );
                }
            }
        }

        return $numberFiles;
    }

    /**
     * Set the HTTP method parameter override flag
     *
     * @param bool $override
     *
     * @return Request
     */
    public function setHttpMethodParameterOverride(bool $override): Request
    {
        $this->methodOverride = $override;

        return $this;
    }

    /**
     * Sets automatic sanitizers/filters for a particular field and for
     * particular methods
     *
     * @param string $name
     * @param array  $filters
     * @param array  $scope
     *
     * @return RequestInterface
     * @throws Exception
     */
    public function setParameterFilters(
        string $name,
        array $filters = [],
        array $scope = []
    ): RequestInterface {
        if (empty($filters)) {
            throw new Exception(
                "Filters have not been defined for '" . $name . "'"
            );
        }

        $filterService = $this->getFilterService();
        foreach ($filters as $sanitizer) {
            if (true !== $filterService->has($sanitizer)) {
                throw new Exception(
                    "Sanitizer '" . $sanitizer . "' does not exist in the filter locator"
                );
            }
        }

        $localScope = $scope;
        if (empty($scope)) {
            $localScope = [
                self::METHOD_GET,
                self::METHOD_PATCH,
                self::METHOD_POST,
                self::METHOD_PUT,
            ];
        }

        foreach ($localScope as $scopeMethod) {
            $this->queryFilters[strtoupper($scopeMethod)][$name] = $filters;
        }

        return $this;
    }

    /**
     * Sets if the `Request::getHttpHost` method must be use strict validation
     * of host name or not
     *
     * @param bool $flag
     *
     * @return RequestInterface
     */
    public function setStrictHostCheck(bool $flag = true): RequestInterface
    {
        $this->strictHostCheck = $flag;

        return $this;
    }

    /**
     * Set trusted proxy
     *
     * @param array $trustedProxies
     * @return RequestInterface
     * @throws Exception
     */
    public function setTrustedProxies(array $trustedProxies): RequestInterface
    {
        $filterService = $this->getFilterService();

        // sanitize IPs
        foreach ($trustedProxies as $trustedProxy) {
            $filtered = $filterService->sanitize($trustedProxy, "ip");
            if ($filtered !== false) {
                $this->trustedProxies[] = $filtered;
            }
        }

        return $this;
    }

    /**
     * Check if an IP address exists in CIDR range
     *
     * @param string $ip The IP address to check.
     * @param string $cidr The CIDR range to compare against.
     * @return bool True if the IP is in range, false otherwise.
     */
    protected function isIpAddressInCIDR(string $ip, string $cidr): bool
    {
        $parts       = explode('/', $cidr);
        $subnet      = $parts[0];
        $maskLength  = $parts[1];

        $ipBin     = inet_pton($ip);
        $subnetBin = inet_pton($subnet);

        if ($ipBin === false || $subnetBin === false) {
            return false; // Invalid IP
        }

        $ipBits        = unpack("H*", $ipBin);
        $subnetBits    = unpack("H*", $subnetBin);

        $ipBits     = $ipBits[1];
        $subnetBits = $subnetBits[1];

        // Convert hex string to binary string
        $ipBits     = hex2bin(str_pad($ipBits, strlen($ipBits), "0"));
        $subnetBits = hex2bin(str_pad($subnetBits, strlen($subnetBits), "0"));

        $maskBytes     = (int)floor($maskLength / 8);
        $remainingBits = $maskLength % 8;

        // Compare full bytes
        if (strncmp($ipBits, $subnetBits, $maskBytes) !== 0) {
            return false;
        }

        if ($remainingBits === 0) {
            return true;
        }

        $ipByte     = ord($ipBits[$maskBytes]);
        $subnetByte = ord($subnetBits[$maskBytes]);

        $tempMask = (1 << (8 - $remainingBits)) - 1;
        $mask = 0xFF ^ $tempMask;

        return ($ipByte & $mask) === ($subnetByte & $mask);
    }

    /**
     * Process a request header and return the one with best quality
     *
     * @param array  $qualityParts
     * @param string $name
     *
     * @return string
     */
    final protected function getBestQuality(
        array $qualityParts,
        string $name
    ): string {
        $counter      = 0;
        $quality      = 0.0;
        $selectedName = '';

        foreach ($qualityParts as $accept) {
            if (0 === $counter) {
                $quality      = (double)$accept['quality'];
                $selectedName = $accept[$name];
            } else {
                $acceptQuality = (double)$accept['quality'];

                if ($acceptQuality > $quality) {
                    $quality      = $acceptQuality;
                    $selectedName = $accept[$name];
                }
            }

            $counter++;
        }

        return $selectedName;
    }

    /**
     * Helper to get data from superglobals, applying filters if needed.
     * If no parameters are given the superglobal is returned.
     *
     * @param array       $source
     * @param string|null $name
     * @param mixed|null  $filters
     * @param mixed|null  $defaultValue
     * @param bool        $notAllowEmpty
     * @param bool        $noRecursive
     *
     * @return mixed
     * @throws Exception
     */
    final protected function getHelper(
        array $source,
        string | null $name = null,
        mixed $filters = null,
        mixed $defaultValue = null,
        bool $notAllowEmpty = false,
        bool $noRecursive = false
    ): mixed {
        if (null === $name) {
            return $source;
        }

        if (!isset($source[$name])) {
            return $defaultValue;
        }

        $value = $source[$name];
        if (
            true !== is_numeric($value) &&
            empty($value) &&
            true === $notAllowEmpty
        ) {
            return $defaultValue;
        }

        if (null !== $filters) {
            $filterService = $this->getFilterService();
            $value         = $filterService->sanitize(
                $value,
                $filters,
                $noRecursive
            );

            /**
             * @todo Leave this here for PHP 7.4/8.0. Remove when appropriate.
             * Some filters use filter_var which can return `false`
             */
            if (false === $value) {
                return $defaultValue;
            }
        }

        return $value;
    }

    /**
     * Process a request header and return an array of values with their
     * qualities
     *
     * @param string $serverIndex
     * @param string $name
     *
     * @return array
     */
    final protected function getQualityHeader(
        string $serverIndex,
        string $name
    ): array {
        $returnedParts = [];
        $serverValue   = $this->getServer($serverIndex);
        $serverValue   = (null === $serverValue) ? '' : $serverValue;

        $parts = preg_split(
            "/,\\s*/",
            $serverValue,
            -1,
            PREG_SPLIT_NO_EMPTY
        );

        foreach ($parts as $part) {
            $headerParts = [];
            $headerSplit = preg_split(
                "/\s*;\s*/",
                trim($part),
                -1,
                PREG_SPLIT_NO_EMPTY
            );

            foreach ($headerSplit as $headerPart) {
                if (str_contains($headerPart, '=')) {
                    $split = explode('=', $headerPart, 2);

                    if ('q' === $split[0]) {
                        $headerParts['quality'] = (double)$split[1];
                    } else {
                        $headerParts[$split[0]] = $split[1];
                    }
                } else {
                    $headerParts[$name]     = $headerPart;
                    $headerParts["quality"] = 1.0;
                }
            }

            $returnedParts[] = $headerParts;
        }

        return $returnedParts;
    }

    /**
     * Recursively counts file in an array of files
     *
     * @param mixed $data
     * @param bool  $onlySuccessful
     *
     * @return int
     */
    final protected function hasFileHelper(
        mixed $data,
        bool $onlySuccessful
    ): int {
        $numberFiles = 0;

        if (!is_array($data)) {
            return 1;
        }

        foreach ($data as $value) {
            if (
                !is_array($value) &&
                (!$value || true !== $onlySuccessful)
            ) {
                $numberFiles++;
            }

            if (is_array($value)) {
                $numberFiles += $this->hasFileHelper(
                    $value,
                    $onlySuccessful
                );
            }
        }

        return $numberFiles;
    }

    /**
     * Resolve authorization headers.
     *
     * @return array
     * @throws EventsException
     */
    protected function resolveAuthorizationHeaders(): array
    {
        $authHeader = null;
        $headers    = [];

        if (
            null !== $this->container &&
            null === $this->eventsManager
        ) {
            $this->eventsManager = $this->container->getShared('eventsManager');
        }

        if (null !== $this->eventsManager) {
            $resolved = $this->fireManagerEvent(
                'request:beforeAuthorizationResolve',
                [
                    'server' => $_SERVER,
                ]
            );

            if (is_array($resolved)) {
                $headers = array_merge($headers, $resolved);
            }
        }

        if (
            true === $this->hasServer('PHP_AUTH_USER') &&
            true === $this->hasServer('PHP_AUTH_PW')
        ) {
            $headers['Php-Auth-User'] = $this->getServer('PHP_AUTH_USER');
            $headers['Php-Auth-Pw']   = $this->getServer('PHP_AUTH_PW');
        } else {
            if (true === $this->hasServer('HTTP_AUTHORIZATION')) {
                $authHeader = $this->getServer('HTTP_AUTHORIZATION');
            } elseif (
                true === $this->hasServer(
                    'REDIRECT_HTTP_AUTHORIZATION'
                )
            ) {
                $authHeader = $this->getServer('REDIRECT_HTTP_AUTHORIZATION');
            }

            if ($authHeader) {
                if (0 === stripos($authHeader, 'basic ')) {
                    $exploded = explode(
                        ":",
                        base64_decode(substr($authHeader, 6)),
                        2
                    );

                    if (2 === count($exploded)) {
                        $headers['Php-Auth-User'] = $exploded[0];
                        $headers['Php-Auth-Pw']   = $exploded[1];
                    }
                } elseif (
                    0 === stripos($authHeader, 'digest ') &&
                    true !== $this->hasServer('PHP_AUTH_DIGEST')
                ) {
                    $headers['Php-Auth-Digest'] = $authHeader;
                } elseif (0 === stripos($authHeader, 'bearer ')) {
                    $headers['Authorization'] = $authHeader;
                }
            }
        }

        if (!isset($headers['Authorization'])) {
            if (isset($headers['Php-Auth-User'])) {
                $headers['Authorization'] = 'Basic '
                    . base64_encode(
                        $headers['Php-Auth-User'] . ':' . $headers['Php-Auth-Pw']
                    );
            } elseif (isset($headers['Php-Auth-Digest'])) {
                $headers['Authorization'] = $headers['Php-Auth-Digest'];
            }
        }

        if (null !== $this->eventsManager) {
            $resolved = $this->fireManagerEvent(
                'request:afterAuthorizationResolve',
                [
                    'headers' => $headers,
                    'server'  => $_SERVER,
                ]
            );

            if (is_array($resolved)) {
                $headers = array_merge($headers, $resolved);
            }
        }

        return $headers;
    }

    /**
     * Smooth out $_FILES as a one dimension array with all files uploaded
     *
     * @param array  $names
     * @param array  $types
     * @param array  $tmpNames
     * @param array  $sizes
     * @param array  $errors
     * @param string $prefix
     *
     * @return array
     */
    final protected function smoothFiles(
        array $names,
        array $types,
        array $tmpNames,
        array $sizes,
        array $errors,
        string $prefix
    ): array {
        $files = [];
        foreach ($names as $index => $name) {
            $key = $prefix . '.' . $index;

            if (is_string($name)) {
                $files[] = [
                    'name'     => $name,
                    'type'     => $types[$index],
                    'tmp_name' => $tmpNames[$index],
                    'size'     => $sizes[$index],
                    'error'    => $errors[$index],
                    'key'      => $key,
                ];
            }

            if (is_array($name)) {
                $parentFiles = $this->smoothFiles(
                    $name,
                    $types[$index],
                    $tmpNames[$index],
                    $sizes[$index],
                    $errors[$index],
                    $key
                );

                foreach ($parentFiles as $file) {
                    $files[] = $file;
                }
            }
        }

        return $files;
    }

    /**
     * Checks the filter service and assigns it to the class parameter
     *
     * @return FilterInterface
     * @throws Exception
     */
    private function getFilterService(): FilterInterface
    {
        if (null === $this->filterService) {
            if (null === $this->container) {
                throw new Exception(
                    "A dependency injection container is required "
                    . "to access the 'filter' service"
                );
            }

            $this->filterService = $this->container->getShared("filter");
        }

        return $this->filterService;
    }

    /**
     * Return post data from rawBody or urlencoded form data
     *
     * @param array|null $data
     * @return array
     */
    private function getPostData(array|null $data): array
    {
        if (empty($data)) {
            if ($this->isJson()) {
                $result = $this->getJsonRawBody(true);
            } else {
                // this is more like a fallback to application/x-www-form-urlencoded parsing raw body
                // the web server should technically take care of adding these to $_POST, but who knows...
                $result = [];
                parse_str($this->getRawBody(), $result);
            }
        } else {
            $result = $data;
        }

        // sanity check, if after all parsing is not an array, set an empty one
        if (!is_array($result)) {
            $result = [];
        }

        return $result;
    }

    /**
     * @param array  $files
     * @param bool   $namedKeys
     * @param array  $input
     * @param string $key
     *
     * @return array
     */
    private function processFiles(
        array $files,
        bool $namedKeys,
        array $input,
        string $key
    ): array {
        if (true === $namedKeys) {
            $files[$key] = new File($input, $key);
        } else {
            $files[] = new File($input, $key);
        }

        return $files;
    }
}
