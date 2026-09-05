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

use Phalcon\Contracts\ADR\ADRTypes;
use Phalcon\Contracts\ADR\Handler;
use Phalcon\Contracts\ADR\Middleware;
use Phalcon\Contracts\Http\AttributeRequest;
use Phalcon\Http\Response;
use Phalcon\Http\ResponseInterface;
use Phalcon\Traits\Support\Helper\Arr\GetTrait;

/**
 * CORS middleware. Inert by default: it emits nothing until an origin allowlist
 * is configured, and only for requests whose `Origin` is on it. The allowed
 * origin is always echoed back explicitly, so credentials are never paired with
 * a wildcard origin. Preflight `OPTIONS` requests are answered directly.
 *
 * @phpstan-import-type adr_cors_config from ADRTypes
 */
class CorsMiddleware implements Middleware
{
    use GetTrait;

    protected bool $allowCredentials = false;

    /**
     * @var list<string>
     */
    protected array $allowedHeaders = [];

    /**
     * @var list<string>
     */
    protected array $allowedMethods = [];

    /**
     * @var list<string>
     */
    protected array $allowedOrigins = [];
    protected int $maxAge           = 0;

    /**
     * @phpstan-param adr_cors_config $config
     */
    public function __construct(array $config = [])
    {
        /** @var list<string> $origins */
        $origins = $this->getArrVal($config, 'origins', []);
        /** @var list<string> $methods */
        $methods = $this->getArrVal(
            $config,
            'methods',
            ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS']
        );
        /** @var list<string> $headers */
        $headers = $this->getArrVal($config, 'headers', ['Content-Type', 'Authorization']);
        /** @var bool $credentials */
        $credentials = $this->getArrVal($config, 'credentials', false, 'bool');
        /** @var int $maxAge */
        $maxAge = $this->getArrVal($config, 'maxAge', 0, 'int');

        $this->allowedOrigins   = $origins;
        $this->allowedMethods   = $methods;
        $this->allowedHeaders   = $headers;
        $this->allowCredentials = $credentials;
        $this->maxAge           = $maxAge;
    }

    public function __invoke(AttributeRequest $request, Handler $next): ResponseInterface
    {
        $origin = $request->getHeader('Origin');

        if (empty($origin) || !$this->isAllowed($origin)) {
            return $next->__invoke($request);
        }

        if ('OPTIONS' === $request->getMethod()) {
            $response = new Response();
            $response->setStatusCode(204);
            $this->applyHeaders($response, $origin);
            $response->setHeader('Access-Control-Allow-Methods', implode(', ', $this->allowedMethods));
            $response->setHeader('Access-Control-Allow-Headers', implode(', ', $this->allowedHeaders));

            if ($this->maxAge > 0) {
                $response->setHeader('Access-Control-Max-Age', (string) $this->maxAge);
            }

            return $response;
        }

        $response = $next->__invoke($request);
        $this->applyHeaders($response, $origin);

        return $response;
    }

    protected function applyHeaders(ResponseInterface $response, string $origin): void
    {
        /**
         * Credentials must never be paired with a reflected wildcard-matched
         * origin: that lets any site read credentialed cross-origin responses
         * (CWE-942), defeating the browser's "*" + credentials block. When
         * credentials are enabled, only an origin that is explicitly on the
         * allowlist may be reflected; a wildcard match emits no CORS headers.
         */
        if (
            $this->allowCredentials &&
            !in_array($origin, $this->allowedOrigins, true)
        ) {
            return;
        }

        $response->setHeader('Access-Control-Allow-Origin', $origin);

        if ($this->allowCredentials) {
            $response->setHeader('Access-Control-Allow-Credentials', 'true');
        }
    }

    protected function isAllowed(string $origin): bool
    {
        if (in_array('*', $this->allowedOrigins, true)) {
            return true;
        }

        return in_array($origin, $this->allowedOrigins, true);
    }
}
