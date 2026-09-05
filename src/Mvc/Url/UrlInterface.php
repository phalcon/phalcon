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
     * @param mixed $uri       URI
     * @param mixed $arguments Optional arguments to be appended to the query string
     */
    public function get(
        mixed $uri = null,
        mixed $arguments = null,
        bool | null $local = null,
        mixed $baseUri = null,
        bool $replaceArgs = false
    ): string;

    /**
     * Returns a base path
     */
    public function getBasePath(): string | null;

    /**
     * Returns the prefix for all the generated urls. By default, /
     */
    public function getBaseUri(): string;

    /**
     * Generates a local path
     */
    public function path(string | null $path = null): string;

    /**
     * Sets a base paths for all the generated paths
     */
    public function setBasePath(string $basePath): UrlInterface;

    /**
     * Sets a prefix to all the urls generated
     */
    public function setBaseUri(string $baseUri): UrlInterface;
}
