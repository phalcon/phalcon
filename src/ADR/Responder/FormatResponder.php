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

namespace Phalcon\ADR\Responder;

use Phalcon\Contracts\ADR\Payload\Payload;
use Phalcon\Contracts\ADR\Responder\Responder;
use Phalcon\Http\RequestInterface;
use Phalcon\Http\ResponseInterface;

/**
 * Negotiates a formatter against the request `Accept` header and renders the
 * payload as the response body + content type.
 *
 * If no formatter accepts the header it falls back to the first (default)
 * formatter, so the content type and body are never left unset.
 */
class FormatResponder implements Responder
{
    /**
     * @var array
     */
    protected $formatters;

    public function __construct(array $formatters = [])
    {
        $this->formatters = $formatters;
    }

    public function __invoke(
        RequestInterface $request,
        ResponseInterface $response,
        Payload $payload
    ): ResponseInterface {
        if (empty($this->formatters)) {
            return $response;
        }

        $accept = (string) $request->getHeader('Accept');
        $chosen = null;

        foreach ($this->formatters as $formatter) {
            if ($formatter->accepts($accept)) {
                $chosen = $formatter;
                break;
            }
        }

        if (null === $chosen) {
            $chosen = $this->formatters[0];
        }

        $response
            ->setContentType($chosen->contentType())
            ->setContent($chosen->format($payload));

        return $response;
    }
}
