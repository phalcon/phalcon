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
use Phalcon\Http\Message\Interfaces\UriInterface;
use Phalcon\Support\Collection;
use Phalcon\Support\Collection\CollectionInterface;

use function array_merge;
use function implode;
use function is_array;
use function is_numeric;
use function is_resource;
use function is_string;
use function preg_match;

/**
 * Message methods
 */
abstract class AbstractMessage extends AbstractCommon implements MessageInterface, ResponseStatusCodeInterface
{
    /**
     * Gets the body of the message.
     *
     * @var StreamInterface
     */
    protected StreamInterface $body;

    /**
     * @var CollectionInterface
     */
    protected CollectionInterface $headers;

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
     * Retrieves the URI instance.
     *
     * This method MUST return a UriInterface instance.
     *
     * @see http://tools.ietf.org/html/rfc3986#section-4.3
     *
     * @var UriInterface
     */
    protected UriInterface $uri;

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
     * This method returns all of the header values of the given
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
        return implode(',', $this->getHeader($name));
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
     * Returns the Uri
     *
     * @return UriInterface
     */
    public function getUri(): UriInterface
    {
        return $this->uri;
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
        $this->checkHeaderName($name);

        $headers  = clone $this->headers;
        $existing = $headers->get($name, []);
        $value    = $this->getHeaderValue($value);
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
        $this->checkHeaderName($name);

        $headers = clone $this->headers;
        $value   = $this->getHeaderValue($value);

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
     * Ensure Host is the first header.
     *
     * @see: http://tools.ietf.org/html/rfc7230#section-5.4
     *
     * @param CollectionInterface $collection
     *
     * @return CollectionInterface
     */
    final protected function checkHeaderHost(
        CollectionInterface $collection
    ): CollectionInterface {
        if (
            true === $collection->has("host") &&
            true !== empty($this->uri) &&
            "" !== $this->uri->getHost()
        ) {
            $host      = $this->getUriHost($this->uri);

            $collection->remove("host");

            $data   = $collection->toArray();
            $header = ["Host" => [$host]] + (array) $data;

            $collection->clear();
            $collection->init($header);
        }

        return $collection;
    }

    /**
     * Check the name of the header. Throw exception if not valid
     *
     * @see http://tools.ietf.org/html/rfc7230#section-3.2
     *
     * @param string $name
     *
     * @return void
     */
    final protected function checkHeaderName(string $name): void
    {
        if (!preg_match("/^[a-zA-Z\d'`#$%&*+.^_|~!-]+$/", $name)) {
            throw new InvalidArgumentException(
                "Invalid header name " . $name
            );
        }
    }

    /**
     * Validates a header value
     *
     * Most HTTP header field values are defined using common syntax
     * components (token, quoted-string, and comment) separated by
     * whitespace or specific delimiting characters.  Delimiters are chosen
     * from the set of US-ASCII visual characters not allowed in a token
     * (DQUOTE and '(),/:;<=>?@[\]{}').
     *
     *     token          = 1*tchar
     *
     *     tchar          = '!' / '#' / '$' / '%' / '&' / ''' / '*'
     *                    / '+' / '-' / '.' / '^' / '_' / '`' / '|' / '~'
     *                    / DIGIT / ALPHA
     *                    ; any VCHAR, except delimiters
     *
     * A string of text is parsed as a single value if it is quoted using
     * double-quote marks.
     *
     *     quoted-string  = DQUOTE *( qdtext / quoted-pair ) DQUOTE
     *     qdtext         = HTAB / SP /%x21 / %x23-5B / %x5D-7E / obs-text
     *     obs-text       = %x80-FF
     *
     * Comments can be included in some HTTP header fields by surrounding
     * the comment text with parentheses.  Comments are only allowed in
     * fields containing 'comment' as part of their field value definition.
     *
     *     comment        = '(' *( ctext / quoted-pair / comment ) ')'
     *     ctext          = HTAB / SP / %x21-27 / %x2A-5B / %x5D-7E / obs-text
     *
     * The backslash octet ('\') can be used as a single-octet quoting
     * mechanism within quoted-string and comment constructs.  Recipients
     * that process the value of a quoted-string MUST handle a quoted-pair
     * as if it were replaced by the octet following the backslash.
     *
     *     quoted-pair    = '\' ( HTAB / SP / VCHAR / obs-text )
     *
     * A sender SHOULD NOT generate a quoted-pair in a quoted-string except
     * where necessary to quote DQUOTE and backslash octets occurring within
     * that string.  A sender SHOULD NOT generate a quoted-pair in a comment
     * except where necessary to quote parentheses ['(' and ')'] and
     * backslash octets occurring within that comment.
     *
     * @see https://tools.ietf.org/html/rfc7230#section-3.2.6
     *
     * @param mixed $value
     *
     * @return void
     */
    final protected function checkHeaderValue($value): void
    {
        if (true !== is_string($value) && true !== is_numeric($value)) {
            throw new InvalidArgumentException("Invalid header value");
        }

        $value = (string) $value;

        if (
            preg_match("#(?:(?:(?<!\r)\n)|(?:\r(?!\n))|(?:\r\n(?![ \t])))#", $value) ||
            preg_match("/[^\x09\x0a\x0d\x20-\x7E\x80-\xFE]/", $value)
        ) {
            throw new InvalidArgumentException("Invalid header value");
        }
    }

    /**
     * Returns the header values checked for validity
     *
     * @param mixed $values
     *
     * @return array
     */
    final protected function getHeaderValue($values): array
    {
        $valueArray = $values;
        if (true !== is_array($values)) {
            $valueArray = [$values];
        }

        if (true === empty($valueArray)) {
            throw new InvalidArgumentException(
                "Invalid header value: must be a string or " .
                "array of strings; cannot be an empty array"
            );
        }

        $valueData = [];
        foreach ($valueArray as $value) {
            $this->checkHeaderValue($value);

            $valueData[] = (string) $value;
        }

        return $valueData;
    }

    /**
     * Return the host and if applicable the port
     *
     * @param UriInterface $uri
     *
     * @return string
     */
    final protected function getUriHost(UriInterface $uri): string
    {
        $host = $uri->getHost();

        if (null !== $uri->getPort()) {
            $host .= ":" . $uri->getPort();
        }

        return $host;
    }

    /**
     * Populates the header collection
     *
     * @param array $headers
     *
     * @return CollectionInterface
     */
    final protected function populateHeaderCollection(array $headers): CollectionInterface
    {
        $collection = new Collection();
        foreach ($headers as $name => $value) {
            $this->checkHeaderName($name);

            $name  = (string) $name;
            $value = $this->getHeaderValue($value);

            $collection->set($name, $value);
        }

        return $collection;
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

        if (true !== is_string($body) && true !== is_resource($body)) {
            throw new InvalidArgumentException(
                "Invalid stream passed as a parameter"
            );
        }

        return new Stream($body, $mode);
    }

    /**
     * Sets the headers
     *
     * @param mixed $headers
     *
     * @return CollectionInterface
     */
    final protected function processHeaders($headers): CollectionInterface
    {
        if (true === is_array($headers)) {
            $collection = $this->populateHeaderCollection($headers);
            $collection = $this->checkHeaderHost($collection);
        } else {
            if (!($headers instanceof CollectionInterface)) {
                throw new InvalidArgumentException(
                    "Headers needs to be either an array or an instance "
                    . "implementing Phalcon\\Support\\CollectionInterface"
                );
            }

            $collection = $headers;
        }

        return $collection;
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
            "3.0" => 1
        ];

        if (true === empty($protocol) || true !== is_string($protocol)) {
            throw new InvalidArgumentException("Invalid protocol value");
        }

        if (true !== isset($protocols[$protocol])) {
            throw new InvalidArgumentException(
                "Unsupported protocol " . $protocol
            );
        }

        return $protocol;
    }
}
