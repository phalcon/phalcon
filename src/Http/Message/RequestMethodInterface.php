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

namespace Phalcon\Http\Message;

/**
 * Interface for Request methods
 *
 * Implementation of this file has been influenced by PHP FIG
 * @link    https://github.com/php-fig/http-message-util/
 * @license https://github.com/php-fig/http-message-util/blob/master/LICENSE
 */
interface RequestMethodInterface
{
    /**
     * @var string
     */
    public const METHOD_CONNECT = "CONNECT";
    /**
     * @var string
     */
    public const METHOD_DELETE  = "DELETE";
    /**
     * @var string
     */
    public const METHOD_GET     = "GET";
    /**
     * @var string
     */
    public const METHOD_HEAD    = "HEAD";
    /**
     * @var string
     */
    public const METHOD_OPTIONS = "OPTIONS";
    /**
     * @var string
     */
    public const METHOD_PATCH   = "PATCH";
    /**
     * @var string
     */
    public const METHOD_POST    = "POST";
    /**
     * @var string
     */
    public const METHOD_PURGE   = "PURGE";
    /**
     * @var string
     */
    public const METHOD_PUT     = "PUT";
    /**
     * @var string
     */
    public const METHOD_TRACE   = "TRACE";
}
