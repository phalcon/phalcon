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

namespace Phalcon\Encryption\Security\JWT;

use Phalcon\Encryption\Security\JWT\Signer\SignerInterface;
use Phalcon\Encryption\Security\JWT\Token\Enum;
use Phalcon\Encryption\Security\JWT\Token\Token;
use Phalcon\Time\Clock\ClockInterface;

/**
 * Class Validator
 */
class Validator
{
    /**
     * @var array<string, int|string|null>
     */
    private array $claims;

    /**
     * @var array<array-key, string>
     */
    private array $errors = [];

    /**
     * Validator constructor.
     *
     * @param Token               $token
     * @param int                 $timeShift Legacy clock-skew offset in seconds
     *                                       added to validated timestamps.
     *                                       Prefer injecting a ClockInterface
     *                                       for testable time; retained for BC.
     * @param ClockInterface|null $clock     Clock used to read "now" at
     *                                       construction. Defaults to the
     *                                       system wall clock (time()).
     */
    public function __construct(
        private Token $token,
        private readonly int $timeShift = 0,
        ClockInterface | null $clock = null
    ) {
        $now = time();
        if (null !== $clock) {
            $now = $clock->now()->getTimestamp();
        }

        $this->claims = [
            Enum::AUDIENCE        => null,
            Enum::EXPIRATION_TIME => $now,
            Enum::ID              => null,
            Enum::ISSUED_AT       => $now,
            Enum::ISSUER          => null,
            Enum::NOT_BEFORE      => $now,
            Enum::SUBJECT         => null,
        ];
    }

    /**
     * Return the value of a claim
     *
     * @param string $claim
     *
     * @return mixed
     */
    public function get(string $claim): mixed
    {
        return $this->claims[$claim] ?? null;
    }

    /**
     * Return an array with validation errors (if any)
     *
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Set the value of a claim, for comparison with the token values
     *
     * @param string $claim
     * @param mixed  $value
     *
     * @return Validator
     */
    public function set(string $claim, mixed $value): static
    {
        $this->claims[$claim] = $value;

        return $this;
    }

    /**
     * Set the token to be validated
     *
     * @param Token $token
     *
     * @return Validator
     */
    public function setToken(Token $token): static
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Validate the audience
     *
     * @param string|string[] $audience
     *
     * @return Validator
     */
    public function validateAudience(array | string $audience): static
    {
        if (is_string($audience)) {
            $audience = [$audience];
        }

        /** @var array $tokenAudience */
        $tokenAudience = $this->token->getClaims()
                                     ->get(Enum::AUDIENCE, [])
        ;

        foreach ($audience as $item) {
            if (true !== in_array($item, $tokenAudience)) {
                $this->errors[] = "Validation: audience not allowed";
            }
        }

        return $this;
    }

    /**
     * Validate a claim
     *
     * @param string          $name
     * @param bool|int|string $value
     *
     * @return Validator
     */
    public function validateClaim(string $name, bool|int|string $value): static
    {
        $claimValue = $this->token->getClaims()->get($name);

        if ($value !== $claimValue) {
            $this->errors[] = "Validation: incorrect $name";
        }

        return $this;
    }

    /**
     * Validate the expiration time of the token
     *
     * @param int $timestamp
     *
     * @return Validator
     */
    public function validateExpiration(int $timestamp): static
    {
        $tokenExpirationTime = (int) $this->token->getClaims()->get(Enum::EXPIRATION_TIME);

        if (
            $this->token->getClaims()->has(Enum::EXPIRATION_TIME) &&
            $this->getTimestamp($timestamp) > $tokenExpirationTime
        ) {
            $this->errors[] = "Validation: the token has expired";
        }

        return $this;
    }

    /**
     * Validate the id of the token
     *
     * @param string $jwtId
     *
     * @return Validator
     */
    public function validateId(string $jwtId): static
    {
        $tokenId = (string)$this->token->getClaims()->get(Enum::ID);

        if ($jwtId !== $tokenId) {
            $this->errors[] = "Validation: incorrect Id";
        }

        return $this;
    }

    /**
     * Validate the issued at (iat) of the token
     *
     * @param int $timestamp
     *
     * @return Validator
     */
    public function validateIssuedAt(int $timestamp): static
    {
        $tokenIssuedAt = (int)$this->token->getClaims()
                                          ->get(Enum::ISSUED_AT)
        ;

        if ($this->getTimestamp($timestamp) <= $tokenIssuedAt) {
            $this->errors[] = "Validation: the token cannot be used yet (future)";
        }

        return $this;
    }

    /**
     * Validate the issuer of the token
     *
     * @param string $issuer
     *
     * @return Validator
     */
    public function validateIssuer(string $issuer): static
    {
        $tokenIssuer = (string)$this->token->getClaims()
                                           ->get(Enum::ISSUER)
        ;

        if ($issuer !== $tokenIssuer) {
            $this->errors[] = "Validation: incorrect issuer";
        }

        return $this;
    }

    /**
     * Validate the notbefore (nbf) of the token
     *
     * @param int $timestamp
     *
     * @return Validator
     */
    public function validateNotBefore(int $timestamp): static
    {
        $tokenNotBefore = (int)$this->token->getClaims()
                                           ->get(Enum::NOT_BEFORE)
        ;

        if ($this->getTimestamp($timestamp) <= $tokenNotBefore) {
            $this->errors[] = "Validation: the token cannot be used yet (not before)";
        }

        return $this;
    }

    /**
     * Validate the signature of the token
     *
     * @param SignerInterface $signer
     * @param string          $passphrase
     *
     * @return Validator
     */
    public function validateSignature(
        SignerInterface $signer,
        string $passphrase
    ): static {
        if (
            true !== $signer->verify(
                $this->token->getSignature()
                            ->getHash(),
                $this->token->getPayload(),
                $passphrase
            )
        ) {
            $this->errors[] = "Validation: the signature does not match";
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
