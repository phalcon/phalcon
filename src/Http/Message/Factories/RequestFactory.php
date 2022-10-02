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

namespace Phalcon\Http\Message\Factories;

use Phalcon\Http\Message\Interfaces\RequestFactoryInterface;
use Phalcon\Http\Message\Interfaces\RequestInterface;
use Phalcon\Http\Message\Interfaces\UriInterface;
use Phalcon\Http\Message\Request;

/**
 * Factory for Request objects
 */
final class RequestFactory implements RequestFactoryInterface
{
    /**
     * Create a new request.
     *
     * @param string                   $method
     * @param UriInterface|string|null $uri
     *
     * @return RequestInterface
     */
    public function createRequest(string $method, $uri): RequestInterface
    {
        return new Request($method, $uri);
    }
}
