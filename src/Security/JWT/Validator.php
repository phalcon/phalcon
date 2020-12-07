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

namespace Phalcon\Security\JWT;

use Phalcon\Security\JWT\Exceptions\ValidatorException;
use Phalcon\Security\JWT\Signer\SignerInterface;
use Phalcon\Security\JWT\Token\Enum;
use Phalcon\Security\JWT\Token\Token;

/**
 * Class Validator
 *
 * @property int   $timeShift
 * @property Token $token
 */
class Validator
{
    /**
     * @var int
     */
    private int $timeShift = 0;

    /**
     * @var Token
     */
    private Token $token;

    /**
     * Validator constructor.
     *
     * @param Token $token
     * @param int   $timeShift
     */
    public function __construct(Token $token, int $timeShift = 0)
    {
        $this->token     = $token;
        $this->timeShift = $timeShift;
    }

    /**
     * @param Token $token
     *
     * @return Validator
     */
    public function setToken(Token $token): Validator
    {
        $this->token = $token;

        return $this;
    }

    /**
     * @param string $audience
     *
     * @return Validator
     * @throws ValidatorException
     */
    public function validateAudience(string $audience): Validator
    {
        $tokenAudience = $this->token->getClaims()->get(Enum::AUDIENCE, []);
        if (true !== in_array($audience, $tokenAudience)) {
            throw new ValidatorException(
                "Validation: audience not allowed"
            );
        }

        return $this;
    }

    /**
     * @param int $timestamp
     *
     * @return Validator
     * @throws ValidatorException
     */
    public function validateExpiration(int $timestamp): Validator
    {
        $tokenExpirationTime = (int) $this->token->getClaims()->get(Enum::EXPIRATION_TIME);
        if (
            $this->token->getClaims()->has(Enum::EXPIRATION_TIME) &&
            $this->getTimestamp($timestamp) >= $tokenExpirationTime
        ) {
            throw new ValidatorException('Validation: the token has expired');
        }

        return $this;
    }

    /**
     * @param string $jwtId
     *
     * @return Validator
     * @throws ValidatorException
     */
    public function validateId(string $jwtId): Validator
    {
        $tokenId = (string) $this->token->getClaims()->get(Enum::ID);
        if ($jwtId !== $tokenId) {
            throw new ValidatorException('Validation: incorrect Id');
        }

        return $this;
    }

    /**
     * @param int $timestamp
     *
     * @return Validator
     * @throws ValidatorException
     */
    public function validateIssuedAt(int $timestamp): Validator
    {
        $tokenIssuedAt = (int) $this->token->getClaims()->get(Enum::ISSUED_AT);
        if ($this->getTimestamp($timestamp) <= $tokenIssuedAt) {
            throw new ValidatorException(
                'Validation: the token cannot be used yet (future)'
            );
        }

        return $this;
    }

    /**
     * @param string $issuer
     *
     * @return Validator
     * @throws ValidatorException
     */
    public function validateIssuer(string $issuer): Validator
    {
        $tokenIssuer = (string) $this->token->getClaims()->get(Enum::ISSUER);
        if ($issuer !== $tokenIssuer) {
            throw new ValidatorException('Validation: incorrect issuer');
        }

        return $this;
    }

    /**
     * @param int $timestamp
     *
     * @return Validator
     * @throws ValidatorException
     */
    public function validateNotBefore(int $timestamp): Validator
    {
        $tokenNotBefore = (int) $this->token->getClaims()->get(Enum::NOT_BEFORE);
        if ($this->getTimestamp($timestamp) <= $tokenNotBefore) {
            throw new ValidatorException(
                'Validation: the token cannot be used yet (not before)'
            );
        }

        return $this;
    }

    /**
     * @param SignerInterface $signer
     * @param string          $passphrase
     *
     * @return Validator
     * @throws ValidatorException
     */
    public function validateSignature(SignerInterface $signer, string $passphrase): Validator
    {
        if (
            true !== $signer->verify(
                $this->token->getSignature()->getHash(),
                $this->token->getPayload(),
                $passphrase
            )
        ) {
            throw new ValidatorException(
                'Validation: the signature does not match'
            );
        }

        return $this;
    }

    /**
     * @param int $timestamp
     *
     * @return int
     */
    private function getTimestamp(int $timestamp): int
    {
        return $timestamp + $this->timeShift;
    }
}
