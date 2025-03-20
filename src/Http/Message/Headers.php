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
use Phalcon\Http\Message\Interfaces\UriInterface;
use Phalcon\Support\Collection;

use function is_array;
use function is_numeric;
use function is_string;
use function preg_match;

/**
 * Message methods
 */
class Headers extends Collection
{
    /**
     * Ensure Host is the first header.
     *
     * @see: https://tools.ietf.org/html/rfc7230#section-5.4
     *
     * @param Headers           $collection
     * @param UriInterface|null $uri
     *
     * @return Headers
     */
    final public function checkHeaderHost(
        Headers $collection,
        UriInterface | null $uri = null
    ): Headers {
        if (
            true === $collection->has("host") &&
            !empty($uri) &&
            "" !== $uri->getHost()
        ) {
            $host = $uri->getHost();

            if (null !== $uri->getPort()) {
                $host .= ":" . $uri->getPort();
            }

            $collection->remove("host");

            $data   = $collection->toArray();
            $header = ["Host" => [$host]] + $data;

            $collection->clear();
            $collection->init($header);
        }

        return $collection;
    }

    /**
     * Check the name of the header. Throw exception if not valid
     *
     * @see https://tools.ietf.org/html/rfc7230#section-3.2
     *
     * @param string $name
     *
     * @return void
     */
    final public function checkHeaderName(string $name): void
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
    final public function checkHeaderValue($value): void
    {
        if (!is_string($value) && !is_numeric($value)) {
            throw new InvalidArgumentException("Invalid header value");
        }

        $value = (string)$value;

        if (
            preg_match(
                "#(?:(?:(?<!\r)\n)|(?:\r(?!\n))|(?:\r\n(?![ \t])))#",
                $value
            ) ||
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
    final public function getHeaderValue($values): array
    {
        $valueArray = $values;
        if (!is_array($values)) {
            $valueArray = [$values];
        }

        if (empty($valueArray)) {
            throw new InvalidArgumentException(
                "Invalid header value: must be a string or " .
                "array of strings; cannot be an empty array"
            );
        }

        $valueData = [];
        foreach ($valueArray as $value) {
            $this->checkHeaderValue($value);

            $valueData[] = (string)$value;
        }

        return $valueData;
    }

    /**
     * Populates the header collection
     *
     * @param array $headers
     *
     * @return Headers
     */
    final public function populateHeaders(array $headers): Headers
    {
        foreach ($headers as $name => $value) {
            $this->checkHeaderName($name);

            $name  = (string)$name;
            $value = $this->getHeaderValue($value);

            $this->set($name, $value);
        }

        return $this;
    }

    /**
     * Sets the headers
     *
     * @param mixed             $headers
     * @param UriInterface|null $uri
     *
     * @return Headers
     */
    final public function processHeaders(
        mixed $headers,
        UriInterface | null $uri = null
    ): Headers {
        if (is_array($headers)) {
            $collection = $this->populateHeaders($headers);
            $collection = $this->checkHeaderHost($collection, $uri);
        } else {
            if (!($headers instanceof Headers)) {
                throw new InvalidArgumentException(
                    "Headers needs to be either an array or an instance "
                    . "of Phalcon\\Http\\Message\\Headers"
                );
            }

            $collection = $headers;
        }

        return $collection;
    }

    /**
     * Internal method to set data
     *
     * @param string $element Name of the element
     * @param mixed  $value   Value to store for the element
     */
    protected function setData(string $element, $value): void
    {
        $this->checkHeaderName($element);
        $value = $this->getHeaderValue($value);

        parent::setData($element, $value);
    }
}
