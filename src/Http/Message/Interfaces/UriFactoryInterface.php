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

declare(strict_types=1);

namespace Phalcon\Http\Message\Interfaces;

use Phalcon\Http\Message\Exception\InvalidArgumentException;

interface UriFactoryInterface
{
    /**
     * Create a new URI.
     *
     * @param string $uri
     *
     * @return UriInterface
     *
     * @throws InvalidArgumentException If the given URI cannot be parsed.
     */
    public function createUri(string $uri = ""): UriInterface;
}
