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

use Phalcon\Contracts\Encryption\EncryptionTypes;
use Phalcon\Encryption\Security\JWT\Exceptions\InvalidAudienceType;
use Phalcon\Encryption\Security\JWT\Exceptions\ValidatorException;
use Phalcon\Encryption\Security\JWT\Signer\SignerInterface;
use Phalcon\Encryption\Security\JWT\Token\Enum;
use Phalcon\Encryption\Security\JWT\Token\Token;
use Phalcon\Time\Clock\ClockInterface;

/**
 * Class Validator
 *
 * @phpstan-import-type encryption_jwt_audience from EncryptionTypes
 * @phpstan-import-type encryption_jwt_errors from EncryptionTypes
 * @phpstan-import-type encryption_jwt_validator_claims from EncryptionTypes
 */
class Validator
{
    /**
     * @phpstan-var encryption_jwt_validator_claims
     */
    private array $claims;

    /**
     * @phpstan-var encryption_jwt_errors
     */
    private array $errors = [];

    /**
     * Validator constructor.
     *
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
     */
    public function get(string $claim): mixed
    {
        return $this->claims[$claim] ?? null;
    }

    /**
     * Return an array with validation errors (if any)
     *
     * @phpstan-return encryption_jwt_errors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Set the value of a claim, for comparison with the token values
     */
    public function set(string $claim, mixed $value): static
    {
        $this->claims[$claim] = $value;

        return $this;
    }

    /**
     * Set the token to be validated
     */
    public function setToken(Token $token): static
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Validate the audience
     *
     * @throws ValidatorException
     */
    public function validateAudience(mixed $audience): static
    {
        if (!is_string($audience) && !is_array($audience)) {
            throw new InvalidAudienceType();
        }

        if (is_string($audience)) {
            $audience = [$audience];
        }

        /** @phpstan-var array<array-key, mixed> $tokenAudience */
        $tokenAudience = $this->token->getClaims()
                                     ->get(Enum::AUDIENCE, [])
        ;

        foreach ($audience as $item) {
            if (true !== in_array($item, $tokenAudience, true)) {
                $this->errors[] = "Validation: audience not allowed";
            }
        }

        return $this;
    }

    /**
     * Validate a claim
     */
    public function validateClaim(string $name, bool | int | string $value): static
    {
        $claimValue = $this->token->getClaims()->get($name);

        if ($value !== $claimValue) {
            $this->errors[] = "Validation: incorrect $name";
        }

        return $this;
    }

    /**
     * Validate the expiration time of the token
     */
    public function validateExpiration(int $timestamp): static
    {
        /** @phpstan-var int|string|null $claimValue */
        $claimValue = $this->token->getClaims()->get(Enum::EXPIRATION_TIME);

        $tokenExpirationTime = (int) $claimValue;

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
     * A null id expresses no expectation and is skipped.
     */
    public function validateId(string | null $jwtId = null): static
    {
        if (null === $jwtId) {
            return $this;
        }

        /** @phpstan-var int|string|null $claimValue */
        $claimValue = $this->token->getClaims()->get(Enum::ID);

        $tokenId = (string) $claimValue;

        if ($jwtId !== $tokenId) {
            $this->errors[] = "Validation: incorrect Id";
        }

        return $this;
    }

    /**
     * Validate the issued at (iat) of the token
     *
     * A token issued at exactly $timestamp is valid. Only a token issued after
     * it, i.e. in the future, is rejected.
     */
    public function validateIssuedAt(int $timestamp): static
    {
        /** @phpstan-var int|string|null $claimValue */
        $claimValue = $this->token->getClaims()
                                  ->get(Enum::ISSUED_AT)
        ;

        $tokenIssuedAt = (int) $claimValue;

        if ($this->getTimestamp($timestamp) < $tokenIssuedAt) {
            $this->errors[] = "Validation: the token cannot be used yet (future)";
        }

        return $this;
    }

    /**
     * Validate the issuer of the token
     *
     * A null issuer expresses no expectation and is skipped.
     */
    public function validateIssuer(string | null $issuer = null): static
    {
        if (null === $issuer) {
            return $this;
        }

        /** @phpstan-var int|string|null $claimValue */
        $claimValue = $this->token->getClaims()
                                  ->get(Enum::ISSUER)
        ;

        $tokenIssuer = (string) $claimValue;

        if ($issuer !== $tokenIssuer) {
            $this->errors[] = "Validation: incorrect issuer";
        }

        return $this;
    }

    /**
     * Validate the notbefore (nbf) of the token
     *
     * A token is valid at exactly $timestamp. Only a timestamp before the
     * "nbf" claim is rejected.
     */
    public function validateNotBefore(int $timestamp): static
    {
        /** @phpstan-var int|string|null $claimValue */
        $claimValue = $this->token->getClaims()
                                  ->get(Enum::NOT_BEFORE)
        ;

        $tokenNotBefore = (int) $claimValue;

        if ($this->getTimestamp($timestamp) < $tokenNotBefore) {
            $this->errors[] = "Validation: the token cannot be used yet (not before)";
        }

        return $this;
    }

    /**
     * Validate the signature of the token
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
     * Validate the subject of the token
     *
     * A null subject expresses no expectation and is skipped.
     */
    public function validateSubject(string | null $subject = null): static
    {
        if (null === $subject) {
            return $this;
        }

        /** @phpstan-var int|string|null $claimValue */
        $claimValue = $this->token->getClaims()
                                  ->get(Enum::SUBJECT)
        ;

        $tokenSubject = (string) $claimValue;

        if ($subject !== $tokenSubject) {
            $this->errors[] = "Validation: incorrect subject";
        }

        return $this;
    }

    private function getTimestamp(int $timestamp): int
    {
        return $timestamp + $this->timeShift;
    }
}
