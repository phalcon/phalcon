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
use Phalcon\Http\Message\Interfaces\ResponseInterface;
use Phalcon\Http\Message\Interfaces\StreamInterface;
use Phalcon\Http\Traits\StatusPhrasesTrait;

/**
 * Response object
 */
class Response extends AbstractMessage implements ResponseInterface
{
    use StatusPhrasesTrait;

    /**
     * Gets the response reason phrase associated with the status code.
     *
     * Because a reason phrase is not a required element in a response
     * status line, the reason phrase value MAY be empty. Implementations MAY
     * choose to return the default RFC 7231 recommended reason phrase (or
     * those
     * listed in the IANA HTTP Status Code Registry) for the response's
     * status code.
     *
     * @see https://tools.ietf.org/html/rfc7231#section-6
     * @see https://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     *
     * @var string
     */
    protected string $reasonPhrase = "";

    /**
     * Gets the response status code.
     *
     * The status code is a 3-digit integer result code of the server's attempt
     * to understand and satisfy the request.
     *
     * @var int
     */
    protected int $statusCode = 200;

    /**
     * Response constructor.
     *
     * @param StreamInterface|resource|string $body
     * @param int                             $code
     * @param array                           $headers
     */
    public function __construct(
        $body = "php://memory",
        int $code = 200,
        array $headers = []
    ) {
        $this->processCode($code);

        $collection    = new Headers();
        $this->headers = $collection->processHeaders($headers);
        $this->body    = $this->processBody($body, "w+b");
    }

    /**
     * @return string
     */
    public function getReasonPhrase(): string
    {
        return $this->reasonPhrase;
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Return an instance with the specified status code and, optionally,
     * reason phrase.
     *
     * If no reason phrase is specified, implementations MAY choose to default
     * to the RFC 7231 or IANA recommended reason phrase for the response's
     * status code.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated status and reason phrase.
     *
     * @see https://tools.ietf.org/html/rfc7231#section-6
     * @see https://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     *
     * @param int    $code
     * @param string $reasonPhrase
     *
     * @return Response
     */
    public function withStatus(
        int $code,
        string $reasonPhrase = ""
    ): ResponseInterface {
        $newInstance = clone $this;

        $newInstance->processCode($code, $reasonPhrase);

        return $newInstance;
    }

    /**
     * Set a valid status code and phrase
     *
     * @param int    $code
     * @param string $phrase
     *
     * @return void
     */
    protected function processCode(int $code, string $phrase = ""): void
    {
        $phrases = $this->getPhrases();
        $this->checkCodeValue($code);

        if ("" === $phrase && isset($phrases[$code])) {
            $phrase = $phrases[$code];
        }

        $this->statusCode   = $code;
        $this->reasonPhrase = $phrase;
    }

    /**
     * Checks if a code is integer or string
     *
     * @param int $code
     */
    private function checkCodeValue(int $code): void
    {
        if (true !== $this->isBetween($code, 100, 599)) {
            throw new InvalidArgumentException(
                "Invalid status code '" . $code . "', (allowed values 100-599)"
            );
        }
    }

    /**
     * @todo Remove this when we get traits
     */
    private function isBetween(int $value, int $from, int $to): bool
    {
        return $value >= $from && $value <= $to;
    }
}
