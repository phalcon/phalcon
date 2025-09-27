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

use Phalcon\Http\Message\Interfaces\RequestInterface;
use Phalcon\Http\Message\Interfaces\RequestMethodInterface;
use Phalcon\Http\Message\Interfaces\StreamInterface;
use Phalcon\Http\Message\Interfaces\UriInterface;
use Phalcon\Http\Message\Stream\Input;
use Phalcon\Support\Collection\CollectionInterface;

/**
 * Request object
 */
class Request extends AbstractRequest implements
    RequestInterface,
    RequestMethodInterface
{
    /**
     * Request constructor.
     *
     * @param string                          $method
     * @param UriInterface|string|null        $uri
     * @param StreamInterface|resource|string $body
     * @param array|CollectionInterface       $headers
     */
    public function __construct(
        string $method = self::METHOD_GET,
        $uri = null,
        $body = "php://memory",
        $headers = []
    ) {
        if ("php://input" === $body) {
            $body = new Input();
        }

        $collection    = new Headers();
        $this->uri     = $this->processUri($uri);
        $this->headers = $collection->processHeaders($headers, $this->uri);
        $this->method  = $this->processMethod($method);
        $this->body    = $this->processBody($body, "w+b");
    }
}
