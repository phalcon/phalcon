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

use Phalcon\Http\Message\Interfaces\ResponseFactoryInterface;
use Phalcon\Http\Message\Interfaces\ResponseInterface;
use Phalcon\Http\Message\Response;

/**
 * Factory for Response objects
 */
final class ResponseFactory implements ResponseFactoryInterface
{
    /**
     * Create a new response.
     *
     * @param int    $code         The HTTP status code. Defaults to 200.
     * @param string $reasonPhrase The reason phrase to associate with the
     *                             status code in the generated response. If
     *                             none is provided, implementations MAY use
     *                             the defaults as suggested in the HTTP
     *                             specification.
     *
     * @return ResponseInterface
     */
    public function createResponse(
        int $code = 200,
        string $reasonPhrase = ""
    ): ResponseInterface {
        return (new Response())->withStatus($code, $reasonPhrase);
    }
}
