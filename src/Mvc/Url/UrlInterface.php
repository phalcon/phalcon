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

namespace Phalcon\Mvc\Url;

/**
 * Interface for Phalcon\Mvc\Url\UrlInterface
 */
interface UrlInterface
{
    /**
     * Generates a URL
     *
     * @param array|string|null $uri       URI
     * @param array|object|null $arguments Optional arguments to be appended to the query string
     * @param bool|null         $local
     *
     * @return string
     */
    public function get(
        array|string $uri = null,
        array|object $arguments = null,
        bool $local = null
    ): string;

    /**
     * Returns a base path
     *
     * @return string|null
     */
    public function getBasePath(): string | null;

    /**
     * Returns the prefix for all the generated urls. By default, /
     *
     * @return string
     */
    public function getBaseUri(): string;

    /**
     * Sets a base paths for all the generated paths
     *
     * @param string $basePath
     *
     * @return UrlInterface
     */
    public function setBasePath(string $basePath): UrlInterface;

    /**
     * Sets a prefix to all the urls generated
     *
     * @param string $baseUri
     *
     * @return UrlInterface
     */
    public function setBaseUri(string $baseUri): UrlInterface;

    /**
     * Generates a local path
     *
     * @param string|null $path
     *
     * @return string
     */
    public function path(?string $path = null): string;
}
