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

namespace Phalcon\Http\Message;

use Phalcon\Http\Message\Exception\InvalidArgumentException;
use Phalcon\Http\Message\Interfaces\RequestInterface;
use Phalcon\Http\Message\Interfaces\RequestMethodInterface;
use Phalcon\Http\Message\Interfaces\UriInterface;

use function is_string;
use function preg_match;
use function strtoupper;

/**
 * Request methods
 *
 * @property string       $method
 * @property null|string  $requestTarget
 * @property UriInterface $uri
 */
abstract class AbstractRequest extends AbstractMessage implements
    RequestInterface,
    RequestMethodInterface
{
    /**
     * Retrieves the HTTP method of the request.
     *
     * @var string
     */
    protected string $method = self::METHOD_GET;

    /**
     * The request-target, if it has been provided or calculated.
     *
     * @var null|string
     */
    protected string | null $requestTarget = null;

    /**
     * Retrieves the URI instance.
     *
     * This method MUST return a UriInterface instance.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-4.3
     *
     * @var UriInterface
     */
    protected UriInterface $uri;

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Retrieves the message's request target.
     *
     * Retrieves the message's request-target either as it will appear (for
     * clients), as it appeared at request (for servers), or as it was
     * specified for the instance (see withRequestTarget()).
     *
     * In most cases, this will be the origin-form of the composed URI, unless a
     * value was provided to the concrete implementation (see
     * withRequestTarget() below).
     *
     * @return string
     */
    public function getRequestTarget(): string
    {
        $requestTarget = $this->requestTarget;

        if (null === $requestTarget) {
            $requestTarget = $this->uri->getPath();

            if (!empty($this->uri->getQuery())) {
                $requestTarget .= "?" . $this->uri->getQuery();
            }

            if (empty($requestTarget)) {
                $requestTarget = "/";
            }
        }

        return $requestTarget;
    }

    /**
     * Returns the Uri object
     *
     * @return UriInterface
     */
    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    /**
     * Return an instance with the provided HTTP method.
     *
     * While HTTP method names are typically all uppercase characters, HTTP
     * method names are case-sensitive and thus implementations SHOULD NOT
     * modify the given string.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * changed request method.
     *
     * @param string $method
     *
     * @return RequestInterface
     * @throws InvalidArgumentException for invalid HTTP methods.
     */
    public function withMethod(string $method): RequestInterface
    {
        $this->processMethod($method);

        return $this->cloneInstance($method, "method");
    }

    /**
     * Return an instance with the specific request-target.
     *
     * If the request needs a non-origin-form request-target — e.g., for
     * specifying an absolute-form, authority-form, or asterisk-form —
     * this method may be used to create an instance with the specified
     * request-target, verbatim.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * changed request target.
     *
     * @see https://tools.ietf.org/html/rfc7230#section-5.3 (for the various
     *     request-target forms allowed in request messages)
     *
     * @param string|null $requestTarget
     *
     * @return RequestInterface
     */
    public function withRequestTarget(string | null $requestTarget): RequestInterface
    {
        if (null === $requestTarget) {
            return $this;
        }

        if (preg_match("/\s/", $requestTarget)) {
            throw new InvalidArgumentException(
                "Invalid request target: cannot contain whitespace"
            );
        }

        return $this->cloneInstance($requestTarget, "requestTarget");
    }

    /**
     * Returns an instance with the provided URI.
     *
     * This method MUST update the Host header of the returned request by
     * default if the URI contains a host component. If the URI does not
     * contain a host component, any pre-existing Host header MUST be carried
     * over to the returned request.
     *
     * You can opt-in to preserving the original state of the Host header by
     * setting `$preserveHost` to `true`. When `$preserveHost` is set to
     * `true`, this method interacts with the Host header in the following
     * ways:
     *
     * - If the Host header is missing or empty, and the new URI contains
     *   a host component, this method MUST update the Host header in the
     *   returned request.
     * - If the Host header is missing or empty, and the new URI does not
     * contain a host component, this method MUST NOT update the Host header in
     * the returned request.
     * - If a Host header is present and non-empty, this method MUST NOT update
     *   the Host header in the returned request.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * new UriInterface instance.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-4.3
     *
     * @param UriInterface $uri
     * @param bool         $preserveHost
     *
     * @return static
     */
    public function withUri(
        UriInterface $uri,
        bool $preserveHost = false
    ): RequestInterface {
        $headers     = clone $this->headers;
        $newInstance = $this->cloneInstance($uri, "uri");

        if (false === $preserveHost) {
            $headers = $headers->checkHeaderHost($headers);

            $newInstance->headers = $headers;
        }

        return $newInstance;
    }

    /**
     * Check the method
     *
     * @param string $method
     *
     * @return string
     */
    final protected function processMethod(string $method = ""): string
    {
        if ("" !== $method) {
            $methods = [
                "CONNECT" => 1,
                "DELETE"  => 1,
                "GET"     => 1,
                "HEAD"    => 1,
                "OPTIONS" => 1,
                "PATCH"   => 1,
                "POST"    => 1,
                "PURGE"   => 1,
                "PUT"     => 1,
                "TRACE"   => 1,
            ];

            $method = strtoupper($method);

            if (!isset($methods[$method])) {
                throw new InvalidArgumentException(
                    "Invalid or unsupported method " . $method
                );
            }
        }

        return $method;
    }

    /**
     * Sets a valid Uri
     *
     * @param UriInterface|string|null $uri
     *
     * @return UriInterface
     * @throws InvalidArgumentException
     */
    final protected function processUri(mixed $uri): UriInterface
    {
        if ($uri instanceof UriInterface) {
            return $uri;
        }

        if (is_string($uri)) {
            return new Uri($uri);
        }

        if (null === $uri) {
            return new Uri();
        }

        throw new InvalidArgumentException(
            "Invalid uri passed as a parameter"
        );
    }
}
