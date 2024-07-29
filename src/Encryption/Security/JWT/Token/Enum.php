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

namespace Phalcon\Encryption\Security\JWT\Token;

/**
 * Constants for Tokens. It offers constants for Headers as well as Claims
 *
 * @link https://tools.ietf.org/html/rfc7519
 */
class Enum
{
    public const ALGO = 'alg';
    /**
     * Claims
     */
    public const AUDIENCE        = 'aud';
    public const CONTENT_TYPE    = 'cty';
    public const EXPIRATION_TIME = 'exp';
    public const ID              = 'jti';
    public const ISSUED_AT       = 'iat';
    public const ISSUER          = 'iss';
    public const NOT_BEFORE      = 'nbf';
    public const SUBJECT         = 'sub';
    /**
     * Headers
     */
    public const TYPE = 'typ';
}
