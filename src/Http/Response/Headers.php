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

namespace Phalcon\Http\Response;

use IteratorAggregate;
use Traversable;

use function array_key_exists;
use function str_contains;

/**
 * This class is a bag to manage the response headers
 */
class Headers implements HeadersInterface, IteratorAggregate
{
    /**
     * @var array
     */
    protected array $headers = [];

    /**
     * @var bool
     */
    protected bool $isSent = false;

    /**
     * Gets a header value from the internal bag
     *
     * @param string $name
     *
     * @return string|bool|null
     * @todo change the raw headers not to return null
     */
    public function get(string $name): string | bool | null
    {
        /**
         * We need to use array_key_exists() here because raw headers have
         * a value of `null
         */
        return array_key_exists(
            $name,
            $this->headers
        ) ? $this->headers[$name] : false;
    }

    /**
     * @return Traversable
     */
    public function getIterator(): Traversable
    {
        foreach ($this->headers as $index => $header) {
            yield $index => $header;
        }
    }

    /**
     * Checks if a header exists
     *
     * @param string $name
     *
     * @return bool
     */
    public function has(string $name): bool
    {
        return array_key_exists($name, $this->headers);
    }

    /**
     * Returns if the headers have already been sent
     *
     * @return bool
     */
    public function isSent(): bool
    {
        return $this->isSent;
    }

    /**
     * Removes a header by its name
     *
     * @param string $name
     *
     * @return HeadersInterface
     */
    public function remove(string $name): HeadersInterface
    {
        unset($this->headers[$name]);

        return $this;
    }

    /**
     * Reset set headers
     *
     * @return void
     */
    public function reset(): void
    {
        $this->headers = [];
    }

    /**
     * Sends the headers to the client
     *
     * @return bool
     */
    public function send(): bool
    {
        if (true === headers_sent() || true === $this->isSent) {
            return false;
        }

        foreach ($this->headers as $header => $value) {
            if (null !== $value) {
                $header .= ': ' . $value;
            } elseif (
                !str_contains($header, ':') &&
                !str_starts_with($header, 'HTTP/')
            ) {
                $header .= ': ';
            }

            header($header);
        }

        $this->isSent = true;

        return true;
    }

    /**
     * Sets a header to be sent at the end of the request
     *
     * @param string $name
     * @param string $value
     *
     * @return HeadersInterface
     */
    public function set(string $name, string $value): HeadersInterface
    {
        $this->headers[$name] = $value;

        return $this;
    }

    /**
     * Sets a raw header to be sent at the end of the request
     *
     * @param string $header
     *
     * @return HeadersInterface
     */
    public function setRaw(string $header): HeadersInterface
    {
        $this->headers[$header] = null;

        return $this;
    }

    /**
     * Returns the current headers as an array
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->headers;
    }
}
