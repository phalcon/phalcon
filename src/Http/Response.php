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

use DateTime;
use DateTimeZone;
use InvalidArgumentException;
use Phalcon\Di\Di;
use Phalcon\Di\DiInterface;
use Phalcon\Di\Injectable;
use Phalcon\Events\EventsAwareInterface;
use Phalcon\Events\Exception as EventsException;
use Phalcon\Events\Traits\EventsAwareTrait;
use Phalcon\Http\Message\Interfaces\ResponseStatusCodeInterface;
use Phalcon\Http\Response\CookiesInterface;
use Phalcon\Http\Response\Exception;
use Phalcon\Http\Response\Headers;
use Phalcon\Http\Response\HeadersInterface;
use Phalcon\Http\Traits\StatusPhrasesTrait;
use Phalcon\Mvc\Url\UrlInterface;
use Phalcon\Mvc\View\ViewInterface;
use Phalcon\Support\Helper\File\Basename;

use function addcslashes;
use function array_keys;
use function json_encode;
use function json_last_error;
use function json_last_error_msg;
use function preg_match;
use function rawurlencode;
use function readfile;
use function strtolower;
use function substr;

use const JSON_ERROR_NONE;

// @todo this will also be removed when traits are available

/**
 * Part of the HTTP cycle is return responses to the clients.
 * Phalcon\HTTP\Response is the Phalcon component responsible to achieve this
 * task. HTTP responses are usually composed by headers and body.
 *
 *```php
 * $response = new \Phalcon\Http\Response();
 *
 * $response->setStatusCode(200, "OK");
 * $response->setContent("<html><body>Hello</body></html>");
 *
 * $response->send();
 *```
 */
class Response extends Injectable implements
    EventsAwareInterface,
    ResponseInterface,
    ResponseStatusCodeInterface
{
    use EventsAwareTrait;
    use StatusPhrasesTrait;

    private const DATETIME_FORMAT = 'D, d M Y H:i:s';
    /**
     * @var CookiesInterface|null
     */
    protected ?CookiesInterface $cookies = null;

    /**
     * @var string|null
     */
    protected string | null $file = null;

    /**
     * @var Headers
     */
    protected Headers $headers;

    /**
     * @var bool
     */
    protected bool $sent = false;

    /**
     * Constructor
     *
     * @param string      $content
     * @param int|null    $code
     * @param string|null $status
     *
     * @throws Exception
     */
    public function __construct(
        protected string $content = '',
        int | null $code = null,
        string | null $status = null
    ) {
        // A Phalcon\Http\Response\Headers bag is temporary used to manage
        // the headers before sent them to the client
        $this->headers = new Headers();

        if (null !== $code) {
            $this->setStatusCode($code, $status);
        }
    }

    /**
     * Appends a string to the HTTP response body
     *
     * @param string $content
     *
     * @return ResponseInterface
     */
    public function appendContent(string $content): ResponseInterface
    {
        $this->content .= $content;

        return $this;
    }

    /**
     * Gets the HTTP response body
     *
     * @return string
     */
    public function getContent(): string
    {
        // Type cast is required here to satisfy the interface
        return $this->content;
    }

    /**
     * Returns cookies set by the user
     *
     * @return CookiesInterface
     */
    public function getCookies(): CookiesInterface
    {
        return $this->cookies;
    }

    /**
     * Returns the internal dependency injector
     *
     * @return DiInterface
     * @throws Exception
     */
    public function getDI(): DiInterface
    {
        if (null === $this->container) {
            $container = Di::getDefault();

            if (null === $container) {
                throw new Exception(
                    "A dependency injection container is required to "
                    . "access the 'url' service"
                );
            }

            $this->container = $container;
        }

        return $this->container;
    }

    /**
     * Returns headers set by the user
     *
     * @return HeadersInterface
     */
    public function getHeaders(): HeadersInterface
    {
        return $this->headers;
    }

    /**
     * Returns the reason phrase
     *
     *```php
     * echo $response->getReasonPhrase();
     *```
     *
     * @return string|null
     */
    public function getReasonPhrase(): string | null
    {
        $statusReasonPhrase = substr($this->headers->get('Status'), 4);

        return $statusReasonPhrase ?: null;
    }

    /**
     * Returns the status code
     *
     *```php
     * echo $response->getStatusCode();
     *```
     *
     * @return int|null
     */
    public function getStatusCode(): int | null
    {
        $statusCode = substr($this->headers->get('Status'), 0, 3);

        return $statusCode ? (int)$statusCode : null;
    }

    /**
     * Checks if a header exists
     *
     *```php
     * $response->hasHeader("Content-Type");
     *```
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasHeader(string $name): bool
    {
        return $this->headers->has($name);
    }

    /**
     * Check if the response is already sent
     *
     * @return bool
     */
    public function isSent(): bool
    {
        return $this->sent;
    }

    /**
     * Redirect by HTTP to another action or URL
     *
     *```php
     * // Using a string redirect (internal/external)
     * $response->redirect("posts/index");
     * $response->redirect("https://en.wikipedia.org", true);
     * $response->redirect("http://www.example.com/new-location", true, 301);
     *
     * // Making a redirection based on a named route
     * $response->redirect(
     *     [
     *         "for"        => "index-lang",
     *         "lang"       => "jp",
     *         "controller" => "index",
     *     ]
     * );
     *```
     *
     * @param string|null $location
     * @param bool        $externalRedirect
     * @param int         $statusCode
     *
     * @return ResponseInterface
     * @throws Exception
     */
    public function redirect(
        string | null $location = null,
        bool $externalRedirect = false,
        int $statusCode = 302
    ): ResponseInterface {
        if (empty($location)) {
            $location = '';
        }

        $header = null;
        if (true === $externalRedirect) {
            $header = $location;
        } else {
            if (str_contains($location, '://')) {
                $matched = preg_match("/^[^:\\/?#]++:/", $location);
                if ($matched) {
                    $header = $location;
                }
            }
        }

        $container = $this->getDI();

        if (empty($header)) {
            /** @var UrlInterface $url */
            $url    = $container->getShared('url');
            $header = $url->get($location);
        }

        if (true === $container->has('view')) {
            $view = $container->getShared('view');

            if ($view instanceof ViewInterface) {
                $view->disable();
            }
        }

        /**
         * The HTTP status is 302 by default, a temporary redirection
         */
        if ($statusCode < 300 || $statusCode > 308) {
            $statusCode = 302;
        }

        $this->setStatusCode($statusCode);

        /**
         * Change the current location using 'Location'
         */
        $this->setHeader('Location', $header);

        return $this;
    }

    /**
     * Remove a header in the response
     *
     *```php
     * $response->removeHeader("Expires");
     *```
     *
     * @param string $name
     *
     * @return ResponseInterface
     */
    public function removeHeader(string $name): ResponseInterface
    {
        $this->headers->remove($name);

        return $this;
    }

    /**
     * Resets all the established headers
     *
     * @return ResponseInterface
     */
    public function resetHeaders(): ResponseInterface
    {
        $this->headers->reset();

        return $this;
    }

    /**
     * Prints out HTTP response to the client
     *
     * @return ResponseInterface
     * @throws EventsException
     * @throws Exception
     */
    public function send(): ResponseInterface
    {
        if (true === $this->sent) {
            throw new Exception('Response was already sent');
        }

        $this
            ->sendHeaders()
            ->sendCookies()
        ;

        /**
         * Output the response body
         */
        if (!empty($this->content)) {
            echo $this->content;
        } else {
            if (!empty($this->file)) {
                readfile($this->file);
            }
        }

        $this->sent = true;

        return $this;
    }

    /**
     * Sends cookies to the client
     *
     * @return ResponseInterface
     */
    public function sendCookies(): ResponseInterface
    {
        $this->cookies?->send();

        return $this;
    }

    /**
     * Sends headers to the client
     *
     * @return ResponseInterface|bool
     * @throws EventsException
     */
    public function sendHeaders(): ResponseInterface | bool
    {
        if (false === $this->fireManagerEvent('response:beforeSendHeaders')) {
            return false;
        }

        /**
         * Send headers
         */
        $result = $this->headers->send();

        if (true === $result) {
            $this->fireManagerEvent('response:afterSendHeaders');
        }

        return $this;
    }

    /**
     * Sets Cache headers to use HTTP cache
     *
     *```php
     * $this->response->setCache(60);
     *```
     *
     * @param int $minutes
     *
     * @return ResponseInterface
     */
    public function setCache(int $minutes): ResponseInterface
    {
        $date = new DateTime();

        $date->modify('+' . $minutes . ' minutes');

        $this->setExpires($date);
        $this->setHeader('Cache-Control', 'max-age=' . ($minutes * 60));

        return $this;
    }

    /**
     * Sets HTTP response body
     *
     *```php
     * $response->setContent("<h1>Hello!</h1>");
     *```
     *
     * @param string $content
     *
     * @return ResponseInterface
     */
    public function setContent(string $content): ResponseInterface
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Sets the response content-length
     *
     *```php
     * $response->setContentLength(2048);
     *```
     *
     * @param int $contentLength
     *
     * @return ResponseInterface
     */
    public function setContentLength(int $contentLength): ResponseInterface
    {
        $this->setHeader('Content-Length', (string)$contentLength);

        return $this;
    }

    /**
     * Sets the response content-type mime, optionally the charset
     *
     *```php
     * $response->setContentType("application/pdf");
     * $response->setContentType("text/plain", "UTF-8");
     *```
     *
     * @param string      $contentType
     * @param string|null $charset
     *
     * @return ResponseInterface
     */
    public function setContentType(
        string $contentType,
        string | null $charset = null
    ): ResponseInterface {
        if (!empty($charset)) {
            $contentType .= '; charset=' . $charset;
        }

        $this->setHeader('Content-Type', $contentType);

        return $this;
    }

    /**
     * Sets a cookies bag for the response externally
     *
     * @param CookiesInterface $cookies
     *
     * @return ResponseInterface
     */
    public function setCookies(CookiesInterface $cookies): ResponseInterface
    {
        $this->cookies = $cookies;

        return $this;
    }

    /**
     * Set a custom ETag
     *
     *```php
     * $response->setEtag(
     *     md5(
     *         time()
     *     )
     * );
     *```
     *
     * @param string $etag
     *
     * @return ResponseInterface
     */
    public function setEtag(string $etag): ResponseInterface
    {
        $this->setHeader('Etag', $etag);

        return $this;
    }

    /**
     * Sets an Expires header in the response that allows to use the HTTP cache
     *
     *```php
     * $this->response->setExpires(
     *     new DateTime()
     * );
     *```
     *
     * @param DateTime $datetime
     *
     * @return ResponseInterface
     */
    public function setExpires(DateTime $datetime): ResponseInterface
    {
        $date = clone $datetime;

        /**
         * All the expiration times are sent in UTC
         * Change the timezone to UTC
         */
        $date->setTimezone(new DateTimeZone('UTC'));

        /**
         * The 'Expires' header set this info
         */
        $this->setHeader(
            'Expires',
            $date->format(self::DATETIME_FORMAT) . ' GMT'
        );

        return $this;
    }

    /**
     * Sets an attached file to be sent at the end of the request
     */
    public function setFileToSend(
        string $filePath,
        string | null $attachmentName = null,
        bool $attach = true
    ): ResponseInterface {
        $basePath = $attachmentName;
        if (empty($attachmentName)) {
            $basePath = (new Basename())($filePath);
        }
        if (true === $attach) {
            $basePathEncoding = mb_detect_encoding(
                $basePath,
                mb_detect_order(),
                true
            );

            $this
                ->setRawHeader('Content-Description: File Transfer')
                ->setRawHeader('Content-Type: application/octet-stream')
                ->setRawHeader('Content-Transfer-Encoding: binary')
            ;

            // According RFC2231 section-7, non-ASCII header param must add an
            // extended one to indicate charset
            if ('ASCII' !== $basePathEncoding) {
                $basePath = rawurlencode($basePath);
                $this->setRawHeader(
                    'Content-Disposition: attachment; filename='
                    . $basePath
                    . "; filename*="
                    . strtolower($basePathEncoding)
                    . "''"
                    . $basePath
                );
            } else {
                // According RFC2045 section-5.1, header param value contains
                // special chars must be as quoted-string. Always quote value
                // is accepted because the special chars is a large list.
                // According RFC822 appendix-D, CR "\" <"> must to be quoted
                // in syntax rule of quoted-string
                $basePath = addcslashes($basePath, "\15\17\\\"");
                $this->setRawHeader(
                    'Content-Disposition: attachment; filename="'
                    . $basePath . '"'
                );
            }
        }

        $this->file = $filePath;

        return $this;
    }

    /**
     * Overwrites a header in the response
     *
     *```php
     * $response->setHeader("Content-Type", "text/plain");
     *```
     *
     * @param string $name
     * @param string $value
     *
     * @return ResponseInterface
     */
    public function setHeader(string $name, string $value): ResponseInterface
    {
        $this->headers->set($name, $value);

        return $this;
    }

    /**
     * Sets a headers bag for the response externally
     *
     * @param HeadersInterface $headers
     *
     * @return ResponseInterface
     */
    public function setHeaders(HeadersInterface $headers): ResponseInterface
    {
        foreach ($headers as $name => $value) {
            $this->headers->set($name, $value);
        }

        return $this;
    }

    /**
     * Sets HTTP response body. The parameter is automatically converted to
     * JSON
     * and also sets default header: Content-Type: "application/json;
     * charset=UTF-8"
     *
     *```php
     * $response->setJsonContent(
     *     [
     *         "status" => "OK",
     *     ]
     * );
     *```
     *
     * @param mixed $content
     * @param int   $jsonOptions
     * @param int   $depth
     *
     * @return ResponseInterface
     */
    public function setJsonContent(
        mixed $content,
        int $jsonOptions = 0,
        int $depth = 512
    ): ResponseInterface {
        $this
            ->setContentType('application/json')
            ->setContent($this->encode($content, $jsonOptions, $depth))
        ;

        return $this;
    }

    /**
     * Sets Last-Modified header
     *
     *```php
     * $this->response->setLastModified(
     *     new DateTime()
     * );
     *```
     *
     * @param DateTime $datetime
     *
     * @return ResponseInterface
     */
    public function setLastModified(DateTime $datetime): ResponseInterface
    {
        $date = clone $datetime;

        /**
         * All the Last-Modified times are sent in UTC
         * Change the timezone to UTC
         */
        $date->setTimezone(new DateTimeZone('UTC'));

        /**
         * The 'Last-Modified' header sets this info
         */
        $this->setHeader(
            'Last-Modified',
            $date->format(self::DATETIME_FORMAT) . ' GMT'
        );

        return $this;
    }

    /**
     * Sends a Not-Modified response
     *
     * @return ResponseInterface
     * @throws Exception
     */
    public function setNotModified(): ResponseInterface
    {
        $this->setStatusCode(304, 'Not modified');

        return $this;
    }

    /**
     * Send a raw header to the response
     *
     *```php
     * $response->setRawHeader("HTTP/1.1 404 Not Found");
     *```
     */
    public function setRawHeader(string $header): ResponseInterface
    {
        $this->headers->setRaw($header);

        return $this;
    }

    /**
     * Sets the HTTP response code
     *
     *```php
     * $response->setStatusCode(404, "Not Found");
     *```
     *
     * @param int         $code
     * @param string|null $message
     *
     * @return ResponseInterface
     * @throws Exception
     */
    public function setStatusCode(
        int $code,
        string | null $message = null
    ): ResponseInterface {
        $currentHeadersRaw = array_keys($this->headers->toArray());

        /**
         * We use HTTP/1.1 instead of HTTP/1.0
         *
         * Before that we would like to unset any existing HTTP/x.y headers
         */
        foreach ($currentHeadersRaw as $key) {
            if (str_contains($key, 'HTTP/')) {
                $this->headers->remove($key);
            }
        }

        // if an empty message is given we try and grab the default for this
        // status code. If a default doesn't exist, stop here.
        if (null === $message) {
            // See: https://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
            $statusCodes = $this->getPhrases();
            if (!isset($statusCodes[$code])) {
                throw new Exception(
                    "Non-standard status-code given without a message"
                );
            }

            $message = $statusCodes[$code];
        }

        $this->headers->setRaw('HTTP/1.1 ' . $code . ' ' . $message);

        /**
         * We also define a 'Status' header with the HTTP status
         */
        $this->headers->set('Status', $code . ' ' . $message);

        return $this;
    }

    /**
     * @todo This will be removed when traits are introduced
     */
    private function encode(
        mixed $data,
        int $options = 0,
        int $depth = 512
    ): string {
        $encoded = json_encode($data, $options, $depth);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new InvalidArgumentException(
                "json_encode error: " . json_last_error_msg()
            );
        }

        return $encoded;
    }
}
