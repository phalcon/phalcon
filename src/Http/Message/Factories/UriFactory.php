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

use Phalcon\Http\Message\Interfaces\UriFactoryInterface;
use Phalcon\Http\Message\Interfaces\UriInterface;
use Phalcon\Http\Message\Uri;

/**
 * Factory for Uri objects
 */
final class UriFactory implements UriFactoryInterface
{
    /**
     * Returns a Uri object
     */
    public function createUri(string $uri = ""): UriInterface
    {
        return new Uri($uri);
    }
}
