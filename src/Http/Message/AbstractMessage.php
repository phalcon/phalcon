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
use Phalcon\Http\Message\Interfaces\MessageInterface;
use Phalcon\Http\Message\Interfaces\ResponseStatusCodeInterface;
use Phalcon\Http\Message\Interfaces\StreamInterface;

use function array_merge;
use function implode;
use function is_resource;
use function is_string;

/**
 * Message methods
 */
abstract class AbstractMessage extends AbstractCommon implements
    MessageInterface,
    ResponseStatusCodeInterface
{
    /**
     * Gets the body of the message.
     *
     * @var StreamInterface
     */
    protected StreamInterface $body;

    /**
     * @var Headers
     */
    protected Headers $headers;

    /**
     * Retrieves the HTTP protocol version as a string.
     *
     * The string MUST contain only the HTTP version number (e.g., '1.1',
     * '1.0').
     *
     * @var string
     */
    protected string $protocolVersion = "1.1";

    /**
     * Return the body of the stream
     *
     * @return StreamInterface
     */
    public function getBody(): StreamInterface
    {
        return $this->body;
    }

    /**
     * Retrieves a message header value by the given case-insensitive name.
     *
     * This method returns an array of all the header values of the given
     * case-insensitive header name.
     *
     * If the header does not appear in the message, this method MUST return an
     * empty array.
     *
     * @param string $name
     *
     * @return array
     */
    public function getHeader(string $name): array
    {
        return $this->headers->get($name, []);
    }

    /**
     * Retrieves a comma-separated string of the values for a single header.
     *
     * This method returns all the header values of the given
     * case-insensitive header name as a string concatenated together using
     * a comma.
     *
     * NOTE: Not all header values may be appropriately represented using
     * comma concatenation. For such headers, use getHeader() instead
     * and supply your own delimiter when concatenating.
     *
     * If the header does not appear in the message, this method MUST return
     * an empty string.
     *
     * @param string $name
     *
     * @return string
     */
    public function getHeaderLine(string $name): string
    {
        return implode(",", $this->getHeader($name));
    }

    /**
     * Retrieves all message header values.
     *
     * The keys represent the header name as it will be sent over the wire, and
     * each value is an array of strings associated with the header.
     *
     *     // Represent the headers as a string
     *     foreach ($message->getHeaders() as $name => $values) {
     *         echo $name . ': ' . implode(', ', $values);
     *     }
     *
     *     // Emit headers iteratively:
     *     foreach ($message->getHeaders() as $name => $values) {
     *         foreach ($values as $value) {
     *             header(sprintf('%s: %s', $name, $value), false);
     *         }
     *     }
     *
     * While header names are not case-sensitive, getHeaders() will preserve the
     * exact case in which headers were originally specified.
     *
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers->toArray();
    }

    /**
     * Returns the protocol version
     *
     * @return string
     */
    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    /**
     * Checks if a header exists by the given case-insensitive name.
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
     * Return an instance with the specified header appended with the given
     * value.
     *
     * Existing values for the specified header will be maintained. The new
     * value(s) will be appended to the existing list. If the header did not
     * exist previously, it will be added.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * new header and/or value.
     *
     * @param string          $name
     * @param string|string[] $value
     *
     * @return MessageInterface
     */
    public function withAddedHeader(string $name, $value): MessageInterface
    {
        $this->headers->checkHeaderName($name);

        $headers  = clone $this->headers;
        $existing = $headers->get($name, []);
        $value    = $this->headers->getHeaderValue($value);
        $value    = array_merge($existing, $value);

        $headers->set($name, $value);

        return $this->cloneInstance($headers, "headers");
    }

    /**
     * Return an instance with the specified message body.
     *
     * The body MUST be a StreamInterface object.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return a new instance that has the
     * new body stream.
     *
     * @param StreamInterface $body
     *
     * @return MessageInterface
     * @throws InvalidArgumentException When the body is not valid.
     *
     */
    public function withBody(StreamInterface $body): MessageInterface
    {
        return $this->cloneInstance(
            $this->processBody($body, "w+b"),
            "body"
        );
    }

    /**
     * Return an instance with the provided value replacing the specified
     * header.
     *
     * While header names are case-insensitive, the casing of the header will
     * be preserved by this function, and returned from getHeaders().
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * new and/or updated header and value.
     *
     * @param string          $name
     * @param string|string[] $value
     *
     * @return MessageInterface
     * @throws InvalidArgumentException for invalid header names or values.
     */
    public function withHeader(string $name, $value): MessageInterface
    {
        $this->headers->checkHeaderName($name);

        $headers = clone $this->headers;
        $value   = $headers->getHeaderValue($value);

        $headers->set($name, $value);

        return $this->cloneInstance($headers, "headers");
    }

    /**
     * Return an instance with the specified HTTP protocol version.
     *
     * The version string MUST contain only the HTTP version number (e.g.,
     * '1.1', '1.0').
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * new protocol version.
     *
     * @param string $version
     *
     * @return MessageInterface
     */
    public function withProtocolVersion(string $version): MessageInterface
    {
        return $this->cloneInstance(
            $this->processProtocol($version),
            "protocolVersion"
        );
    }

    /**
     * Return an instance without the specified header.
     *
     * Header resolution MUST be done without case-sensitivity.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that removes
     * the named header.
     *
     * @param string $name
     *
     * @return MessageInterface
     */
    public function withoutHeader(string $name): MessageInterface
    {
        $headers = clone $this->headers;

        $headers->remove($name);

        return $this->cloneInstance($headers, "headers");
    }

    /**
     * Set a valid stream
     *
     * @param StreamInterface|resource|string $body
     * @param string                          $mode
     *
     * @return StreamInterface
     */
    final protected function processBody(
        $body = "php://memory",
        string $mode = "r+b"
    ): StreamInterface {
        if ($body instanceof StreamInterface) {
            return $body;
        }

        if (!is_string($body) && !is_resource($body)) {
            throw new InvalidArgumentException(
                "Invalid stream passed as a parameter"
            );
        }

        return new Stream($body, $mode);
    }

    /**
     * Checks the protocol
     *
     * @param string $protocol
     *
     * @return string
     */
    final protected function processProtocol(string $protocol = ""): string
    {
        $protocols = [
            "1.0" => 1,
            "1.1" => 1,
            "2.0" => 1,
            "3.0" => 1,
        ];

        if (empty($protocol)) {
            throw new InvalidArgumentException("Invalid protocol value");
        }

        if (!isset($protocols[$protocol])) {
            throw new InvalidArgumentException(
                "Unsupported protocol " . $protocol
            );
        }

        return $protocol;
    }
}
