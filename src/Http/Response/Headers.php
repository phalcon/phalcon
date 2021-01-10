<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace Phiz\Http\Response;

/**
 * Phiz\Http\Response\Headers
 *
 * This class is a bag to manage the response headers
 *
 */
class Headers implements HeadersInterface
{
    protected $headers = [];

    /**
     * Gets a header value from the internal bag
     *  TODO : return string | bool
     */
    public function get(string $name)
    {
       $headerValue = $this->headers[$name] ?? null;
        if (is_null($headerValue)) {
            return false;
        }
        return $headerValue;
    }

    /**
     * Sets a header to be sent at the end of the request
     */
    public function has(string $name) : bool
    {
        return isset ($this->headers[$name]);
    }

    /**
     * Removes a header to be sent at the end of the request
     */
    public function remove(string $header) : HeadersInterface
    {
        unset($this->headers[$header]);

        return $this;
    }

    /**
     * Reset set headers
     */
    public function reset()
    {
        $this->headers = [];
    }

    /**
     * Sends the headers to the client
     */
    public function send() : bool
    {
       // var header, value;

        if (headers_sent()) {
            return false;
        }

        foreach($this->headers as $header => $value):
            if ($value !== null) {
                header( $header . ": " . $value, true );
            } else {
                if ((strpos($header, ":") !== false) || substr($header, 0, 5) == "HTTP/") {
                    header($header, true);
                } else {
                    header(  $header . ": ", true);
                }
            }
       endforeach;

        return true;
    }

    /**
     * Sets a header to be sent at the end of the request
     */
    public function set(string $name, string $value)  : HeadersInterface
    {
        $this->headers[$name] = $value;

        return $this;
    }

    /**
     * Sets a raw header to be sent at the end of the request
     */
    public function setRaw(string $header) : HeadersInterface
    {
         $this->headers[$header] = null;

        return $this;
    }

    /**
     * Returns the current headers as an array
     */
    public function toArray() : array
    {
        return $this->headers;
    }
}
