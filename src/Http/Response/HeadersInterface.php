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
 * Phiz\Http\Response\HeadersInterface
 *
 * Interface for Phiz\Http\Response\Headers compatible bags
 */
interface HeadersInterface
{
    /**
     * Gets a header value from the internal bag
     * TODO: return string | bool;
     */
    public function get(string $name) ;

    /**
     * Returns true if the header is set, false otherwise
     */
    public function has(string $name) : bool;

    /**
     * Reset set headers
     */
    public function reset();

    /**
     * Sends the headers to the client
     */
    public function send() : bool;

    /**
     * Sets a header to be sent at the end of the request
     */
    public function set(string $name, string $value);

    /**
     * Sets a raw header to be sent at the end of the request
     */
    public function setRaw(string $header);
}
