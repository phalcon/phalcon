<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Based on the Action Domain Responder pattern
 * @link    https://pmjones.io/adr/
 */

declare(strict_types=1);

namespace Phalcon\ADR\Middleware;

use Phalcon\Contracts\ADR\Handler;
use Phalcon\Contracts\ADR\Middleware;
use Phalcon\Contracts\Http\AttributeRequest;
use Phalcon\Http\ResponseInterface;

use function in_array;
use function is_scalar;
use function method_exists;
use function strtoupper;

/**
 * Thin enabler for the native `_method` override.
 *
 * `Request::getMethod()` already honors `X-HTTP-Method-Override` and, when the
 * parameter-override flag is on, the `_method` field. This middleware only
 * turns that flag on, and only for a `POST` request whose `_method` names a
 * safe verb (`PUT`/`PATCH`/`DELETE`), so `_method` cannot spoof an arbitrary
 * method.
 *
 * The flag lives on `Phalcon\Http\Request`, not on the request contract, so a
 * request implementation that does not carry it is simply passed through.
 */
class MethodOverrideMiddleware implements Middleware
{
    /**
     * @var array<int, string>
     */
    protected array $allowed = ['DELETE', 'PATCH', 'PUT'];

    public function __invoke(
        AttributeRequest $request,
        Handler $next
    ): ResponseInterface {
        if ('POST' === $request->getMethod()) {
            $method  = $request->getPost('_method');
            $spoofed = strtoupper(is_scalar($method) ? (string) $method : '');

            if (
                in_array($spoofed, $this->allowed, true)
                && method_exists($request, 'setHttpMethodParameterOverride')
            ) {
                $request->setHttpMethodParameterOverride(true);
            }
        }

        return $next->__invoke($request);
    }
}
