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
 * Token Class.
 *
 * A container for Token related data. It stores the claims, headers, signature
 * and payload. It also calculates and returns the token string.
 *
 * @property Item      $claims
 * @property Item      $headers
 * @property Signature $signature
 *
 * @link https://tools.ietf.org/html/rfc7519
 */
class Token
{
    /**
     * @var Item
     */
    private Item $claims;

    /**
     * @var Item
     */
    private Item $headers;

    /**
     * @var Signature
     */
    private Signature $signature;

    /**
     * Token constructor.
     *
     * @param Item      $headers
     * @param Item      $claims
     * @param Signature $signature
     */
    public function __construct(
        Item $headers,
        Item $claims,
        Signature $signature
    ) {
        $this->headers   = $headers;
        $this->claims    = $claims;
        $this->signature = $signature;
    }

    /**
     * @return Item
     */
    public function getClaims(): Item
    {
        return $this->claims;
    }

    /**
     * @return Item
     */
    public function getHeaders(): Item
    {
        return $this->headers;
    }

    /**
     * @return string
     */
    public function getPayload(): string
    {
        return $this->headers->getEncoded() . '.' . $this->claims->getEncoded();
    }


    /**
     * @return Signature
     */
    public function getSignature(): Signature
    {
        return $this->signature;
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->getPayload() . '.' . $this->signature->getEncoded();
    }
}
