<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace Phalcon\Http;

use Phalcon\Di\DiInterface;
use Phalcon\Di\AbstractInjectionAware;
use Phalcon\Events\ManagerInterface;
use Phalcon\Filter\FilterInterface;
use Phalcon\Helper\Json;
use Phalcon\Http\Request\File;
use Phalcon\Http\Request\FileInterface;
use Phalcon\Http\Request\Exception;

use UnexpectedValueException;
use stdClass;
use const PREG_SPLIT_NO_EMPTY;

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
class Request extends AbstractInjectionAware implements RequestInterface
{
    private $filterService;

    /**
     * @var bool
     */
    private $httpMethodParameterOverride = false; // TODO: { get, set };

    /**
     * @var array
     */
    private $queryFilters = [];

    private $putCache;

    private $rawBody;

    /**
     * @var bool
     */
    private $strictHostCheck = false;

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
     */
    public function get(string $name = null, $filters = null, $defaultValue = null, bool $notAllowEmpty = false, bool $noRecursive = false) // TODO: mixed
    {
        return $this->getHelper(
            _REQUEST,
            name,
            filters,
            defaultValue,
            notAllowEmpty,
            noRecursive
        );
    }

    /**
     * Gets an array with mime/types and their quality accepted by the
     * browser/client from _SERVER["HTTP_ACCEPT"]
     */
    public function getAcceptableContent(): array
    {
        return $this->getQualityHeader("HTTP_ACCEPT", "accept");
    }

    /**
     * Gets auth info accepted by the browser/client from
     * $_SERVER["PHP_AUTH_USER"]
     */
    public function getBasicAuth(): ?array 
    {
        if (!$this->hasServer("PHP_AUTH_USER") || !$this->hasServer("PHP_AUTH_PW")){
            return null;
        }

        return [
            "username" =>$this->getServer("PHP_AUTH_USER"),
            "password"=> $this->getServer("PHP_AUTH_PW")
        ];
    }

    /**
     * Gets best mime/type accepted by the browser/client from
     * _SERVER["HTTP_ACCEPT"]
     */
    public function getBestAccept(): string
    {
        return $this->getBestQuality($this->getAcceptableContent(), "accept");
    }

    /**
     * Gets best charset accepted by the browser/client from
     * _SERVER["HTTP_ACCEPT_CHARSET"]
     */
    public function getBestCharset(): string
    {
        return $this->getBestQuality($this->getClientCharsets(), "charset");
    }

    /**
     * Gets best language accepted by the browser/client from
     * _SERVER["HTTP_ACCEPT_LANGUAGE"]
     */
    public function getBestLanguage(): string
    {
        return $this->getBestQuality($this->getLanguages(), "language");
    }

    /**
     * Gets most possible client IPv4 Address. This method searches in
     * `$_SERVER["REMOTE_ADDR"]` and optionally in
     * `$_SERVER["HTTP_X_FORWARDED_FOR"]`
     * TODO: return string | bool
     */
    public function getClientAddress(bool $trustForwardedHeader = false): ?string
    {
        $address = null;
        $server = $this->getServerArray();

        /**
         * Proxies uses this IP
         */
        if ($trustForwardedHeader) {
            $address = $server["HTTP_X_FORWARDED_FOR"] ?? null;

            if ($address === null) {
                $address = $server["HTTP_CLIENT_IP"];
            }
        }

       if ($address === null)  {
            $address = $server["REMOTE_ADDR"];
        }

        if( !is_string($address)){
            return null;
        }

        if (strpos($address, ",") !== false) {
            /**
             * The client address has multiples parts, only return the first
             * part
             */
            return explode(",", $address)[0];
        }

        return $address;
    }

    /**
     * Gets a charsets array and their quality accepted by the browser/client
     * from _SERVER["HTTP_ACCEPT_CHARSET"]
     */
    public function getClientCharsets(): array
    {
        return $this->getQualityHeader("HTTP_ACCEPT_CHARSET", "charset");
    }

    /**
     * Gets content type which request has been made
     */
    public function getContentType(): ?string
    {
        $server = $this->getServerArray();
        $contentType = $server["CONTENT_TYPE"] ?? null;
        return contentType;
    }

    /**
     * Gets auth info accepted by the browser/client from
     * $_SERVER["PHP_AUTH_DIGEST"]
     */
    public function getDigestAuth(): array
    {
       $auth   = [];
        $server = $this->getServerArray();
       $digest = server["PHP_AUTH_DIGEST"] ?? null;
        if ($digest !== null) {
            $matches = [];

            if (!preg_match_all("#(\\w+)=(['\"]?)([^'\" ,]+)\\2#", $digest, $matches, 2)) {
                return $auth;
            }

            if (is_array($matches)) {
                foreach($matches as $match) {
                     $auth[$match[1]] = $match[3];
                }
            }
        }

        return $auth;
    }

    /**
     * Retrieves a query/get value always sanitized with the preset filters
     */
    public function getFilteredQuery(string $name = null, $defaultValue = null, bool $notAllowEmpty = false, bool $noRecursive = false) // TODO: mixed
    {
        //var filters;
        $filters = $this->queryFilters["get"][$name] ?? [];

        return $this->getQuery(
            $name,
            $filters,
            $defaultValue,
            $notAllowEmpty,
            $noRecursive
        );
    }

    /**
     * Retrieves a post value always sanitized with the preset filters
     */
    public function getFilteredPost(string $name = null, $defaultValue = null, bool $notAllowEmpty = false, bool $noRecursive = false) // TODO: mixed
    {
        //var filters;
          $filters = $this->queryFilters["post"][$name] ?? [];

        return $this->getPost(
            $name,
            $filters,
            $defaultValue,
            $notAllowEmpty,
            $noRecursive
        );
    }

    /**
     * Retrieves a put value always sanitized with the preset filters
     */
    public function getFilteredPut(string $name = null, $defaultValue = null, bool $notAllowEmpty = false, bool $noRecursive = false) // TODO: mixed
    {
       $filters = $this->queryFilters["put"][$name] ?? [];
       return $this->getPut(
            $name,
            $filters,
            $defaultValue,
            $notAllowEmpty,
            $noRecursive
        );
    }

    /**
     * Gets HTTP header from request data
     */
    final public function getHeader(string $header): string
    {
        //var value, name, server;

        $name = strtoupper(
            strtr($header, "-", "_")
        );

        $server = $this->getServerArray();
        $value = $server[$name] ?? null;
        if ($value !== null) {
            return $value;
        }
        $value = $server["HTTP_" . $name] ?? "";
        return $value;
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
     */
    public function getHeaders(): array
    {
        //var name, value, authHeaders, server;

        $headers = [];

        $contentHeaders = [
            "CONTENT_TYPE" =>   true,
            "CONTENT_LENGTH"  => true,
            "CONTENT_MD5"  => true
        ];

        $server = $this->getServerArray();

        foreach($server as $name => $value) {
            // Note: The starts_with uses case insensitive search here
            if (str_starts_with($name, "HTTP_")) {
                $name = ucwords(
                    strtolower(
                        str_replace( "_", " ",  substr($name, 5))
                    )
                );

                $name = str_replace(" ", "-", $name);

                $headers[$name] = $value;

                continue;
            }

            // The "CONTENT_" headers are not prefixed with "HTTP_".
            $name = strtoupper($name);

            if( isset( $contentHeaders[$name])) {
                $name = ucwords(
                    strtolower(
                        str_replace("_", " ", name)
                    )
                );

                $name = str_replace(" ", "-", $name);

                $headers[$name] = $value;
            }
        }

        $authHeaders = $this->resolveAuthorizationHeaders();

        // Protect for future (child classes) changes
        $headers = array_merge($headers, $authHeaders);

        return $headers;
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
        //var host, strict, cleanHost;

        $strict = $this->strictHostCheck;

        /**
         * Get the server name from $_SERVER["HTTP_HOST"]
         */
        $host = $this->getServer("HTTP_HOST");

        if (empty($host)) {
            /**
             * Get the server name from $_SERVER["SERVER_NAME"]
             */
            $host = $this->getServer("SERVER_NAME");
            if(empty($host)) {
                /**
                 * Get the server address from $_SERVER["SERVER_ADDR"]
                 */
                $host = $this->getServer("SERVER_ADDR");
            }
        }

        if (empty($host) && $strict) {
            /**
             * Cleanup. Force lowercase as per RFC 952/2181
             */
            $cleanHost = strtolower( trim($host) );

            if (strpos($cleanHost, ":") !== false) {
                $cleanHost = preg_replace("/:[[:digit:]]+$/", "", $cleanHost);
            }

            /**
             * Host may contain only the ASCII letters 'a' through 'z'
             * (in a case-insensitive manner), the digits '0' through '9', and
             * the hyphen ('-') as per RFC 952/2181
             */
            if (!empty( preg_replace("/[a-z0-9-]+\.?/", "", $cleanHost))) {
                throw new UnexpectedValueException("Invalid host " . $host);
            }
        } else {
            $cleanHost = $host;
        }

        return (string) $cleanHost;
    }

    /**
     * Gets web page that refers active request. ie: http://www.google.com
     */
    public function getHTTPReferer(): string
    {
        //var httpReferer, server;

        $server = $this->getServerArray();
        $httpReferer = server["HTTP_REFERER"] ?? "";
        return $httpReferer;
    }

    /**
     * Gets decoded JSON HTTP raw request body
     * TODO: return <\stdClass> | array | bool
     */
    public function getJsonRawBody(bool $associative = false)
    {
      
        $rawBody = $this->getRawBody();

        if (!is_string($rawBody)) {
            return false;
        }

        return json_decode($rawBody, $associative);
    }

    /**
     * Gets languages array and their quality accepted by the browser/client
     * from _SERVER["HTTP_ACCEPT_LANGUAGE"]
     */
    public function getLanguages(): array
    {
        return $this->getQualityHeader("HTTP_ACCEPT_LANGUAGE", "language");
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
     */
    final public function getMethod(): string
    {
        //var overridedMethod, spoofedMethod, requestMethod, server;
        $returnMethod = "";

        $server = $this->getServerArray();
        $requestMethod = $server["REQUEST_METHOD"] ?? null;
        if (empty($requestMethod)) {
            return "GET";
        }
        else {
            $returnMethod = strtoupper($requestMethod);
        }

        if ("POST" === $returnMethod) {
            $overridedMethod = $this->getHeader("X-HTTP-METHOD-OVERRIDE");
            if (!empty($overridedMethod)) {
                $returnMethod = strtoupper($overridedMethod);
            } else
                if ($this->httpMethodParameterOverride) {
                    $spoofedMethod = REQUEST["_method"] ?? null;
                    if ($spoofedMethod !== null) {
                         $returnMethod = strtoupper($spoofedMethod);
                    }
            }
        }

        if ( !$this->isValidHttpMethod($returnMethod)) {
            return "GET";
        }

        return $returnMethod;
    }

    /**
     * Gets information about the port on which the request is made.
     */
    public function getPort() : int
    {
        //var host, pos;

        /**
         * Get the server name from $_SERVER["HTTP_HOST"]
         */
        $host = $this->getServer("HTTP_HOST");

        if (!$host) {
            return (int) $this->getServer("SERVER_PORT");
        }

      
        $pos = strrpos($host, ":");

        if (false !== $pos) {
            return (int) substr($host, $pos + 1);
        }
        return ("https" === $this->getScheme()) ? 443 : 80;
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
     */
    public function getPost(string $name = null, $filters = null, $defaultValue = null, bool $notAllowEmpty = false, bool $noRecursive = false) // TODO: mixed
    {
        return $this->getHelper(
            $_POST,
            $name,
            $filters,
            $defaultValue,
            $notAllowEmpty,
            $noRecursive
        );
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
     */
    public function getPut(string $name = null, $filters = null, $defaultValue = null, bool $notAllowEmpty = false, bool $noRecursive = false) // TODO: mixed
    {
        //var put, contentType;

        $put = $this->putCache;

        if (!is_array($put)) {
            $contentType = $this->getContentType();

            if (!empty($contentType) && stripos($contentType, "json") != false) {
                $put = $this->getJsonRawBody(true);

                if (!is_array($put)){
                    $put = [];
                }
            } else {
                $put = [];
                parse_str($this->getRawBody(), $put);
            }

            $this->putCache = $put;
        }

        return $this->getHelper(
            $put,
            $name,
            $filters,
            $defaultValue,
            $notAllowEmpty,
            $noRecursive
        );
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
     */
    public function getQuery(string $name = null, $filters = null, $defaultValue = null, bool $notAllowEmpty = false, bool $noRecursive = false) // TODO: mixed
    {
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
     */
    public function getRawBody(): string
    {
        //var rawBody, contents;

        $rawBody = $this->rawBody;

        if (empty($rawBody)) {
            $contents = file_get_contents("php://input");

            /**
             * We need store the read raw body because it can't be read again
             */
            $this->rawBody = $contents;

            return $contents;
        }

        return $rawBody;
    }

    /**
     * Gets HTTP schema (http/https)
     */
    public function getScheme(): string
    {
        //var https;

        $https = $this->getServer("HTTPS");

        if ($https !== "off") {
            return "https";
        }

        return "http";
    }

    /**
     * Gets variable from $_SERVER superglobal
     */
    public function getServer(string $name): ?string
    {
        //var serverValue, server;

        $server = $this->getServerArray();
        $serverValue = $server[$name] ?? null;
        return $serverValue;
    }

    /**
     * Gets active server address IP
     */
    public function getServerAddress(): string
    {
        //var serverAddr;

        $serverAddr = $this->getServer("SERVER_ADDR");

        if (null === $serverAddr) {
            return gethostbyname("localhost");
        }

        return $serverAddr;
    }

    /**
     * Gets active server name
     */
    public function getServerName(): string
    {
        //var serverName;

        $serverName = $this->getServer("SERVER_NAME");

        if (null === $serverName) {
            return "localhost";
        }

        return $serverName;
    }

    /**
     * Gets attached files as Phalcon\Http\Request\File instances
     */
    public function getUploadedFiles(bool $onlySuccessful = false, bool $namedKeys = false) : array
    {
        //var superFiles, prefix, input, smoothInput, file, dataFile;
       $files = [];

        $superFiles = $_FILES;

        if (count($superFiles) > 0) {
            foreach($superFiles as $prefix =>  $input) {
                if (is_array($input["name"])) {
                    $smoothInput = $this->smoothFiles(
                        $input["name"],
                        $input["type"],
                        $input["tmp_name"],
                        $input["size"],
                        $input["error"],
                        $prefix
                    );
                    foreach($smoothInput as $file) {
                        if ( ($onlySuccessful === false) || ($file["error"] === UPLOAD_ERR_OK)) {
                            $dataFile = [
                                "name" =>    $file["name"],
                                "type"  =>      $file["type"],
                                "tmp_name" =>  $file["tmp_name"],
                                "size" =>    $file["size"],
                                "error" =>     $file["error"]
                            ];

                            if ($namedKeys === true) {
                                $files[ $file["key"]] = new File( $dataFile, $file["key"]  );
                            } else {
                                $files[] = new File( $dataFile,  $file["key"]);
                            }
                        }
                    }
                }
                elseif ( ($onlySuccessful == false) || ($input["error"] === UPLOAD_ERR_OK)){
                        $f = File($input, $prefix);
                        if ($namedKeys === true):
                            $files[$prefix] = $f;
                        else:
                            $files[] =$f;
                        endif;
                }
            }
        }
        return $files;
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
     * @param bool onlyPath If true, query part will be omitted
     * @return string
     */
    final public function getURI(bool $onlyPath = false): string
    {
        //var requestURI;

        $requestURI = $this->getServer("REQUEST_URI");
        if (null === $requestURI) {
            return "";
        }

        if ($onlyPath) {
            $requestURI = explode('?', $requestURI)[0];
        }

        return $requestURI;
    }

    /**
     * Gets HTTP user agent used to made the request
     */
    public function getUserAgent(): string
    {
       // var userAgent;

        $userAgent = $this->getServer("HTTP_USER_AGENT");
        if (null === $userAgent) {
            return "";
        }

        return $userAgent;
    }

    /**
     * Checks whether $_REQUEST superglobal has certain index
     */
    public function has(string $name): bool
    {
        return isset( $_REQUEST[$name]);
    }

    /**
     * Returns if the request has files or not
     */
    public function hasFiles(): bool
    {
        return $this->numFiles(true) > 0;
    }

    /**
     * Checks whether headers has certain index
     */
    final public function hasHeader(string $header): bool
    {
        //var name;

        $name = strtoupper(strtr($header, "-", "_"));

        return $this->hasServer($name) || $this->hasServer("HTTP_" . $name);
    }

    /**
     * Checks whether $_POST superglobal has certain index
     */
    public function hasPost(string $name): bool
    {
        return isset( $_POST[$name]);
    }

    /**
     * Checks whether the PUT data has certain index
     */
    public function hasPut(string $name): bool
    {
        //var put;

        $put = $this->getPut();

        return isset ($put[$name]);
    }

    /**
     * Checks whether $_GET superglobal has certain index
     */
    public function hasQuery(string $name): bool
    {
        return isset ($_GET[$name]);
    }

    /**
     * Checks whether $_SERVER superglobal has certain index
     */
    final public function hasServer(string $name): bool
    {
        //var server;

        $server = $this->getServerArray();

        return isset ($server[$name]);
    }

    /**
     * Checks whether request has been made using ajax
     */
    public function isAjax(): bool
    {
        return $this->hasServer("HTTP_X_REQUESTED_WITH") 
                        && $this->getServer("HTTP_X_REQUESTED_WITH") === "XMLHttpRequest";
    }

    /**
     * Checks whether HTTP method is CONNECT.
     * if _SERVER["REQUEST_METHOD"]==="CONNECT"
     */
    public function isConnect(): bool
    {
        return $this->getMethod() === "CONNECT";
    }

    /**
     * Checks whether HTTP method is DELETE.
     * if _SERVER["REQUEST_METHOD"]==="DELETE"
     */
    public function isDelete(): bool
    {
        return $this->getMethod() === "DELETE";
    }

    /**
     * Checks whether HTTP method is GET.
     * if _SERVER["REQUEST_METHOD"]==="GET"
     */
    public function isGet(): bool
    {
        return $this->getMethod() === "GET";
    }

    /**
     * Checks whether HTTP method is HEAD.
     * if _SERVER["REQUEST_METHOD"]==="HEAD"
     */
    public function isHead(): bool
    {
        return $this->getMethod() === "HEAD";
    }

    /**
     * Check if HTTP method match any of the passed methods
     * When strict is true it checks if validated methods are real HTTP methods
     */
    public function isMethod($methods, bool $strict = false): bool
    {
        //var httpMethod, method;

        $httpMethod = $this->getMethod();

        if (is_string($methods)) {
            if ($strict && !$this->isValidHttpMethod($methods)) {
                throw new Exception("Invalid HTTP method: " . $methods);
            }

            return ($methods === $httpMethod);
        }

        if (is_array($methods)) {
            foreach( $methods as $method) {
                if ($this->isMethod($method, $strict)) {
                    return true;
                }
            }
            return false;
        }

        if ($strict) {
            throw new Exception("Invalid HTTP method: non-string");
        }

        return false;
    }

    /**
     * Checks whether HTTP method is OPTIONS.
     * if _SERVER["REQUEST_METHOD"]==="OPTIONS"
     */
    public function isOptions(): bool
    {
        return $this->getMethod() === "OPTIONS";
    }

    /**
     * Checks whether HTTP method is PATCH.
     * if _SERVER["REQUEST_METHOD"]==="PATCH"
     */
    public function isPatch(): bool
    {
        return $this->getMethod() === "PATCH";
    }

    /**
     * Checks whether HTTP method is POST.
     * if _SERVER["REQUEST_METHOD"]==="POST"
     */
    public function isPost(): bool
    {
        return $this->getMethod() === "POST";
    }

    /**
     * Checks whether HTTP method is PUT.
     * if _SERVER["REQUEST_METHOD"]==="PUT"
     */
    public function isPut(): bool
    {
        return $this->getMethod() === "PUT";
    }

    /**
     * Checks whether HTTP method is PURGE (Squid and Varnish support).
     * if _SERVER["REQUEST_METHOD"]==="PURGE"
     */
    public function isPurge(): bool
    {
        return $this->getMethod() === "PURGE";
    }

    /**
     * Checks whether request has been made using any secure layer
     */
    public function isSecure(): bool
    {
        return $this->getScheme() === "https";
    }

    /**
     * Checks if the `Request::getHttpHost` method will be use strict validation
     * of host name or not
     */
    public function isStrictHostCheck(): bool
    {
        return $this->strictHostCheck;
    }

    /**
     * Checks whether request has been made using SOAP
     */
    public function isSoap(): bool
    {
        //var contentType;

        if ($this->hasServer("HTTP_SOAPACTION")) {
            return true;
        }

        $contentType = $this->getContentType();

        if (empty ($contentType)) {
            return false;
        }

        return strpos($contentType, "application/soap+xml") !== false;
    }

    /**
     * Checks whether HTTP method is TRACE.
     * if _SERVER["REQUEST_METHOD"]==="TRACE"
     */
    public function isTrace(): bool
    {
        return $this->getMethod() === "TRACE";
    }

    /**
     * Checks if a method is a valid HTTP method
     */
    public function isValidHttpMethod(string $method): bool
    {
        switch (strtoupper($method)) {
            case "GET":
            case "POST":
            case "PUT":
            case "DELETE":
            case "HEAD":
            case "OPTIONS":
            case "PATCH":
            case "PURGE": // Squid and Varnish support
            case "TRACE":
            case "CONNECT":
                return true;
        }

        return false;
    }

    /**
     * Returns the number of files available
     */
    public function numFiles(bool $onlySuccessful = false) : int
    {
        //var files, file, error;
        $numberFiles = 0;

        $files = $_FILES;

        if ( !is_array($files)){
            return 0;
        }

        foreach( $files as $file) {
            $error = $file["error"] ?? false;
            if (!is_array($error)) {
                if (!$error || !$onlySuccessful) {
                    $numberFiles++;
                }
            }
            else {
                $numberFiles += $this->hasFileHelper(
                    $error,
                    $onlySuccessful
                );
            }
        }

        return $numberFiles;
    }

    /**
     * Sets automatic sanitizers/filters for a particular field and for
     * particular methods
     */
    public function setParameterFilters(string $name, array $filters = [], array $scope = []) : RequestInterface
    {
        //var filterService, sanitizer, localScope, scopeMethod;

        if (count($filters) < 1) {
            throw new Exception(
                "Filters have not been defined for '" . $name . "'"
            );
        }

        $filterService = $this->getFilterService();

        foreach($filters as $sanitizer) {
            if (true !== $filterService->has($sanitizer)) {
                throw new Exception(
                    "Sanitizer '" . $sanitizer . "' does not exist in the filter locator"
                );
            }
        }

        if (count($scope) < 1) {
            $localScope = ["get", "post", "put"];
        } else {
            $localScope = $scope;
        }

        foreach( $localScope as $scopeMethod) {
            $this->queryFilters[$scopeMethod][$name] = $filters;
        }

        return this;
    }

    /**
     * Sets if the `Request::getHttpHost` method must be use strict validation
     * of host name or not
     */
    public function setStrictHostCheck(bool $flag = true) : RequestInterface
    {
        $this->strictHostCheck = $flag;

        return this;
    }

    /**
     * Process a request header and return the one with best quality
     */
    final protected function getBestQuality(array $qualityParts, string $name): string
    {
        /* int i;
        double quality, acceptQuality;
        var selectedName, accept; */

        $i = 0;
            $quality = 0.0;
            $selectedName = "";

        foreach($qualityParts as $accept) {
            if ($i == 0) {
                $quality = (double) $accept["quality"];
                    $selectedName = $accept[$name];
            } else {
                $acceptQuality = (double) $accept["quality"];

                if ($acceptQuality > $quality) {
                    $quality = $acceptQuality;
                        $selectedName = $accept[$name];
                }
            }

            $i++;
        }

        return $selectedName;
    }

    /**
     * Helper to get data from superglobals, applying filters if needed.
     * If no parameters are given the superglobal is returned.
     */
    final protected function getHelper(
        array $source,
        string $name = null,
        $filters = null,
        $defaultValue = null,
        bool $notAllowEmpty = false,
        bool $noRecursive = false
    ) // TODO: mixed
   {
        //var value, filterService;

        if ($name === null) {
            return $source;
        }
        $value = $source[$name] ?? null;
        if ( null === $value) {
            return $defaultValue;
        }

        if (!is_numeric($value) && empty($value) && $notAllowEmpty) {
            return $defaultValue;
        }

        if ($filters !== null) {
            $filterService = $this->getFilterService();
                $value         = $filterService->sanitize($value, $filters, $noRecursive);
        }

        return $value;
    }

    /**
     * Recursively counts file in an array of files
     */
    final protected function hasFileHelper($data, bool $onlySuccessful) : int
    {
        $numberFiles = 0;

        if (!is_array($data)) {
            return 1;
        }

        foreach($data as $value) {
            if (!is_array($value)){
                if(empty($value) || !$onlySuccessful) {
                    $numberFiles++;
                }
            }
            else {
                $numberFiles += $this->hasFileHelper($value, $onlySuccessful);
            }
        }

        return $numberFiles;
    }

    /**
     * Process a request header and return an array of values with their qualities
     */
    final protected function getQualityHeader(string $serverIndex, string $name): array
    {
        //var returnedParts, parts, part, headerParts, headerPart, split;

        $returnedParts = [];

        $parts = preg_split(
            "/,\\s*/",
            $this->getServer($serverIndex),
            -1,
            PREG_SPLIT_NO_EMPTY
        );

        foreach($parts as $part){
            $headerParts = [];
            $rs = preg_split("/\s*;\s*/", trim($part), -1, PREG_SPLIT_NO_EMPTY);
            foreach($rs as $headerPart) {
                if (strpos(headerPart, "=") !== false){
                    $split = explode("=", $headerPart, 2);

                    if ($split[0] === "q") {
                        $headerParts["quality"] = (double) $split[1];
                    } else {
                        $headerParts[$split[0]] = $split[1];
                    }
                } else {
                    $headerParts[$name] = $headerPart;
                    $headerParts["quality"] = 1.0;
                }
            }

            $returnedParts[] = $headerParts;
        }

        return $returnedParts;
    }

    /**
     * Resolve authorization headers.
     */
    protected function resolveAuthorizationHeaders(): array
    {
       /* var resolved, eventsManager, hasEM, container, exploded,
            digest, authHeader = null, server; */
        
        $headers = [];

        $container =  $this->getDI();
         $server    = $this->getServerArray();

        // TODO: Make Request implements EventsAwareInterface for v4.0.0
        if (is_object($container)) {
            $hasEM = $container->has("eventsManager");
            
            if ($hasEM) {
                $eventsManager = $container->getShared("eventsManager");
                $hasEM = is_object($eventsManager);
            }
        }

        if ($hasEM) {
            $resolved = $eventsManager->fire(
                "request:beforeAuthorizationResolve",
                $this,
                [
                    "server" => $server
                ]
            );

            if (is_array($resolved )) {
                $headers = array_merge($headers, $resolved);
            }
        }

        if ($this->hasServer("PHP_AUTH_USER") && $this->hasServer("PHP_AUTH_PW")) {
            $headers["Php-Auth-User"] = $this->getServer("PHP_AUTH_USER");
            $headers["Php-Auth-Pw"]   = $this->getServer("PHP_AUTH_PW");
        } else {
            if ($this->hasServer("HTTP_AUTHORIZATION")) {
                $authHeader = $this->getServer("HTTP_AUTHORIZATION");
            } elseif ($this->hasServer("REDIRECT_HTTP_AUTHORIZATION")) {
                $authHeader = $this->getServer("REDIRECT_HTTP_AUTHORIZATION");
            }

            if (authHeader) {
                if (stripos($authHeader, "basic ") === 0) {
                    $exploded = explode(
                        ":",
                        base64_decode(
                            substr($authHeader, 6)
                        ),
                        2
                    );

                    if (count($exploded) == 2) {
                        $headers["Php-Auth-User"] = exploded[0];
                       $headers["Php-Auth-Pw"]   = exploded[1];
                    }
                } elseif (stripos(authHeader, "digest ") === 0 && !$this->hasServer("PHP_AUTH_DIGEST")) {
                    $headers["Php-Auth-Digest"] = authHeader;
                } elseif (stripos(authHeader, "bearer ") === 0) {
                    $headers["Authorization"] = authHeader;
                }
            }
        }

        if (!isset ($headers["Authorization"])) {
            if (isset ($headers["Php-Auth-User"])) {
                $headers["Authorization"] = "Basic " . base64_encode($headers["Php-Auth-User"] . ":" . $headers["Php-Auth-Pw"]);
            } else {
                $digest = $headers["Php-Auth-Digest"] ?? null;
                if (null !== $digest) {
                    $headers["Authorization"] = digest;
                }
            }
        }

        if ($hasEM) {
            $resolved = $eventsManager->fire(
                "request:afterAuthorizationResolve",
                $this,
                [
                    "headers"=> $headers,
                    "server" =>  $server
                ]
            );

            if (is_array($resolved)) {
                $headers = array_merge($headers, $resolved);
            }
        }

        return $headers;
    }

    /**
     * Smooth out $_FILES to have plain array with all files uploaded
     */
    final protected function smoothFiles(array $names, array $types, 
            array $tmp_names,array $sizes, 
            array $errors, string $prefix): array
    {
        //var idx, name, file, files, parentFiles, p;

        $files = [];

        foreach($names as $idx => $name){
            $p = $prefix . "." . $idx;

            if (is_string($name)) {
                $files[] = [
                    "name" =>    $name,
                    "type"=>    $types[$idx],
                    "tmp_name"=>    $tmp_names[$idx],
                    "size"=>    $sizes[$idx],
                    "error"=>    $errors[$idx],
                    "key"=>    $p
                ];
            }

            if (is_array($name)) {
                $parentFiles = $this->smoothFiles(
                    $names[idx],
                    $types[idx],
                    $tmp_names[idx],
                    $sizes[idx],
                    $errors[idx],
                    $p
                );

                foreach($parentFiles as $file) {
                    $files[] = $file;
                }
            }
        }

        return $files;
    }

    /**
     * Checks the filter service and assigns it to the class parameter
     */
    private function getFilterService() :  FilterInterface 
    {
        //var container, filterService;

        $filterService = $this->filterService;

        if (!is_object($filterService)) {
            $container = $this->container;

            if(!is_object($container)) {
                throw new Exception(
                    Exception::containerServiceNotFound("the 'filter' service")
                );
            }

            $filterService   = $container->getShared("filter");
            $this->filterService = $filterService;
        }

        return $this->filterService;
    }

    private function getServerArray(): array
    {
        if ($_SERVER) {
            return $_SERVER;
        } else {
            return [];
        }
    }
}
