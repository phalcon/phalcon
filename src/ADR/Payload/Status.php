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
 *
 * Implementation of this file has been influenced by phalcon-api and AuraPHP
 * @link    https://github.com/phalcon/phalcon-api
 * @license https://github.com/phalcon/phalcon-api/blob/master/LICENSE
 * @link    https://github.com/auraphp/Aura.Payload
 * @license https://github.com/auraphp/Aura.Payload/blob/3.x/LICENSE
 *
 * @see Original inspiration for the https://github.com/phalcon/phalcon-api
 */

declare(strict_types=1);

namespace Phalcon\ADR\Payload;

/**
 * Holds the status codes for the payload.
 *
 * The two failure-related statuses are distinct, following the Aura.Payload
 * lineage:
 *
 * - `ERROR` means an exception was raised while the domain layer was running.
 *   By convention, `Payload::withException()` pairs with the `ERROR` status.
 * - `FAILURE` means the domain layer ran to completion but declined the
 *   request (for example, a business rule was not satisfied); no exception
 *   was raised.
 *
 * @see Payload
 */
class Status
{
    /**
     * @var string
     */
    public const ACCEPTED           = 'ACCEPTED';
    /**
     * @var string
     */
    public const AUTHENTICATED      = 'AUTHENTICATED';
    /**
     * @var string
     */
    public const AUTHORIZED         = 'AUTHORIZED';
    /**
     * @var string
     */
    public const CREATED            = 'CREATED';
    /**
     * @var string
     */
    public const DELETED            = 'DELETED';
    /**
     * @var string
     */
    public const ERROR              = 'ERROR';
    /**
     * @var string
     */
    public const FAILURE            = 'FAILURE';
    /**
     * @var string
     */
    public const FOUND              = 'FOUND';
    /**
     * @var string
     */
    public const METHOD_NOT_ALLOWED = 'METHOD_NOT_ALLOWED';
    /**
     * @var string
     */
    public const NOT_ACCEPTED       = 'NOT_ACCEPTED';
    /**
     * @var string
     */
    public const NOT_AUTHENTICATED  = 'NOT_AUTHENTICATED';
    /**
     * @var string
     */
    public const NOT_AUTHORIZED     = 'NOT_AUTHORIZED';
    /**
     * @var string
     */
    public const NOT_CREATED        = 'NOT_CREATED';
    /**
     * @var string
     */
    public const NOT_DELETED        = 'NOT_DELETED';
    /**
     * @var string
     */
    public const NOT_FOUND          = 'NOT_FOUND';
    /**
     * @var string
     */
    public const NOT_UPDATED        = 'NOT_UPDATED';
    /**
     * @var string
     */
    public const NOT_VALID          = 'NOT_VALID';
    /**
     * @var string
     */
    public const PROCESSING         = 'PROCESSING';
    /**
     * @var string
     */
    public const SUCCESS            = 'SUCCESS';
    /**
     * @var string
     */
    public const UPDATED            = 'UPDATED';
    /**
     * @var string
     */
    public const VALID              = 'VALID';

    /**
     * Instantiation not allowed.
     */
    final private function __construct()
    {
    }
}
