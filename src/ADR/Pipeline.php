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

namespace Phalcon\ADR;

use Phalcon\Contracts\ADR\Handler;
use Phalcon\Contracts\ADR\Middleware;
use Phalcon\Contracts\Http\AttributeRequest;
use Phalcon\Http\ResponseInterface;

/**
 * Self-recursive middleware runner. It is itself a Handler: it carries an index
 * and hands a new Pipeline (advanced by one) forward as the `next` handler, so
 * `next` is always a real Handler - no anonymous classes or callables.
 *
 * When the middleware is exhausted it invokes the terminal handler (the Action).
 */
final class Pipeline implements Handler
{
    /**
     * @param list<Middleware> $middleware
     */
    public function __construct(
        protected array $middleware,
        protected Handler $terminal,
        protected int $index = 0
    ) {
    }

    public function __invoke(AttributeRequest $request): ResponseInterface
    {
        if ($this->index >= count($this->middleware)) {
            return $this->terminal->__invoke($request);
        }

        $mw   = $this->middleware[$this->index];
        $next = new Pipeline($this->middleware, $this->terminal, $this->index + 1);

        return $mw->__invoke($request, $next);
    }
}
