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

use Phalcon\Encryption\Security\JWT\Signer\SignerInterface;
use Phalcon\Encryption\Security\JWT\Validator;

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
     * Return the registered claims
     *
     * @return Item
     */
    public function getClaims(): Item
    {
        return $this->claims;
    }

    /**
     * Return the registered headers
     *
     * @return Item
     */
    public function getHeaders(): Item
    {
        return $this->headers;
    }

    /**
     * Return the payload
     *
     * @return string
     */
    public function getPayload(): string
    {
        return $this->headers->getEncoded() . '.' . $this->claims->getEncoded();
    }

    /**
     * Return the signature
     *
     * @return Signature
     */
    public function getSignature(): Signature
    {
        return $this->signature;
    }

    /**
     * Return the token
     *
     * @return string
     */
    public function getToken(): string
    {
        return $this->getPayload() . '.' . $this->signature->getEncoded();
    }

    /**
     * @param Validator $validator
     *
     * @return array
     */
    public function validate(Validator $validator): array
    {
        $methods = [
            "validateAudience"   => $validator->get(Enum::AUDIENCE),
            "validateExpiration" => $validator->get(Enum::EXPIRATION_TIME),
            "validateId"         => $validator->get(Enum::ID),
            "validateIssuedAt"   => $validator->get(Enum::ISSUED_AT),
            "validateIssuer"     => $validator->get(Enum::ISSUER),
            "validateNotBefore"  => $validator->get(Enum::NOT_BEFORE),
        ];

        foreach ($methods as $method => $claimId) {
            $validator->$method($claimId);
        }

        return $validator->getErrors();
    }

    /**
     * Verify the signature
     *
     * @param SignerInterface $signer
     * @param string          $key
     *
     * @return bool
     */
    public function verify(SignerInterface $signer, string $key): bool
    {
        if (
            $signer->getAlgHeader() !== $this->getHeaders()->get(Enum::ALGO)
        ) {
            return false;
        }

        return $signer->verify(
            $this->signature->getHash(),
            $this->getPayload(),
            $key
        );
    }
}
