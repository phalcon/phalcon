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

/**
 * Interface for Request methods
 *
 * Implementation of this file has been influenced by PHP FIG
 *
 * @link    https://github.com/php-fig/http-message-util/
 * @license https://github.com/php-fig/http-message-util/blob/master/LICENSE
 */
interface RequestMethodInterface
{
    public const METHOD_CONNECT = "CONNECT";
    public const METHOD_DELETE  = "DELETE";
    public const METHOD_GET     = "GET";
    public const METHOD_HEAD    = "HEAD";
    public const METHOD_OPTIONS = "OPTIONS";
    public const METHOD_PATCH   = "PATCH";
    public const METHOD_POST    = "POST";
    public const METHOD_PURGE   = "PURGE";
    public const METHOD_PUT     = "PUT";
    public const METHOD_TRACE   = "TRACE";
}
