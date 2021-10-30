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

use Phalcon\Encryption\Security\JWT\Exceptions\ValidatorException;
use Phalcon\Encryption\Security\JWT\Signer\SignerInterface;
use Phalcon\Encryption\Security\JWT\Token\Enum;
use Phalcon\Encryption\Security\JWT\Token\Item;
use Phalcon\Encryption\Security\JWT\Token\Signature;
use Phalcon\Encryption\Security\JWT\Token\Token;
use Phalcon\Support\Collection;
use Phalcon\Support\Collection\CollectionInterface;
use Phalcon\Support\Traits\Base64Trait;

use function is_array;
use function is_string;
use function json_encode;

/**
 * Builder
 *
 * The builder offers
 *
 * @property CollectionInterface $claims
 * @property CollectionInterface $jose
 * @property string              $passphrase
 * @property SignerInterface     $signer
 *
 * @link https://tools.ietf.org/html/rfc7519
 */
class Builder
{
    use Base64Trait;

    /**
     * @var CollectionInterface
     */
    private CollectionInterface $claims;

    /**
     * @var CollectionInterface
     */
    private CollectionInterface $jose;

    /**
     * @var string
     */
    private string $passphrase;

    /**
     * @var SignerInterface
     */
    private SignerInterface $signer;

    /**
     * Builder constructor.
     *
     * @param SignerInterface $signer
     */
    public function __construct(SignerInterface $signer)
    {
        $this->init();
        $this->signer = $signer;
        $this->jose->set(Enum::ALGO, $this->signer->getAlgHeader());
    }

    /**
     * @return Builder
     */
    public function init(): Builder
    {
        $this->passphrase = '';
        $this->claims     = new Collection();
        $this->jose       = new Collection(
            [
                Enum::TYPE => 'JWT',
                Enum::ALGO => 'none',
            ]
        );

        return $this;
    }

    /**
     * @return array|string
     */
    public function getAudience()
    {
        return $this->claims->get(Enum::AUDIENCE);
    }

    /**
     * @return array
     */
    public function getClaims(): array
    {
        return $this->claims->toArray();
    }

    /**
     * @return string|null
     */
    public function getContentType(): ?string
    {
        return $this->jose->get(Enum::CONTENT_TYPE, null, 'string');
    }

    /**
     * @return int|null
     */
    public function getExpirationTime(): ?int
    {
        return $this->claims->get(Enum::EXPIRATION_TIME, null, 'int');
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->jose->toArray();
    }

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->claims->get(Enum::ID, null, 'string');
    }

    /**
     * @return int|null
     */
    public function getIssuedAt(): ?int
    {
        return $this->claims->get(Enum::ISSUED_AT, null, 'int');
    }

    /**
     * @return string|null
     */
    public function getIssuer(): ?string
    {
        return $this->claims->get(Enum::ISSUER, null, 'string');
    }

    /**
     * @return int|null
     */
    public function getNotBefore(): ?int
    {
        return $this->claims->get(Enum::NOT_BEFORE, null, 'int');
    }

    /**
     * @return string|null
     */
    public function getSubject(): ?string
    {
        return $this->claims->get(Enum::SUBJECT, null, 'string');
    }

    /**
     * @return Token
     * @throws ValidatorException
     */
    public function getToken(): Token
    {
        if (true === empty($this->passphrase)) {
            throw new ValidatorException('Invalid passphrase (empty)');
        }

        $encodedClaims    = $this->doEncodeUrl(json_encode($this->getClaims()));
        $urlClaims        = new Item($this->getClaims(), $encodedClaims);
        $encodedHeaders   = $this->doEncodeUrl(json_encode($this->getHeaders()));
        $urlHeaders       = new Item($this->getHeaders(), $encodedHeaders);
        $signatureHash    = $this->signer->sign(
            $encodedHeaders . '.' . $encodedClaims,
            $this->passphrase
        );
        $encodedSignature = $this->doEncodeUrl($signatureHash);
        $signature        = new Signature($signatureHash, $encodedSignature);

        return new Token($urlHeaders, $urlClaims, $signature);
    }

    /**
     * @return string
     */
    public function getPassphrase(): string
    {
        return $this->passphrase;
    }

    /**
     * The "aud" (audience) claim identifies the recipients that the JWT is
     * intended for.  Each principal intended to process the JWT MUST
     * identify itself with a value in the audience claim.  If the principal
     * processing the claim does not identify itself with a value in the
     * "aud" claim when this claim is present, then the JWT MUST be
     * rejected.  In the general case, the "aud" value is an array of case-
     * sensitive strings, each containing a StringOrURI value.  In the
     * special case when the JWT has one audience, the "aud" value MAY be a
     * single case-sensitive string containing a StringOrURI value.  The
     * interpretation of audience values is generally application specific.
     * Use of this claim is OPTIONAL.
     *
     * @param mixed $audience
     *
     * @return Builder
     * @throws ValidatorException
     */
    public function setAudience($audience): Builder
    {
        if (true !== is_string($audience) && true !== is_array($audience)) {
            throw new ValidatorException('Invalid Audience');
        }

        if (true === is_string($audience)) {
            $audience = [$audience];
        }

        return $this->setClaim(Enum::AUDIENCE, $audience);
    }

    /**
     * Sets the content type header 'cty'
     *
     * @param string $contentType
     *
     * @return Builder
     */
    public function setContentType(string $contentType): Builder
    {
        $this->jose->set(Enum::CONTENT_TYPE, $contentType);

        return $this;
    }

    /**
     * The "exp" (expiration time) claim identifies the expiration time on
     * or after which the JWT MUST NOT be accepted for processing.  The
     * processing of the "exp" claim requires that the current date/time
     * MUST be before the expiration date/time listed in the "exp" claim.
     * Implementers MAY provide for some small leeway, usually no more than
     * a few minutes, to account for clock skew.  Its value MUST be a number
     * containing a NumericDate value.  Use of this claim is OPTIONAL.
     *
     * @param int $timestamp
     *
     * @return Builder
     * @throws ValidatorException
     */
    public function setExpirationTime(int $timestamp): Builder
    {
        if ($timestamp < time()) {
            throw new ValidatorException('Invalid Expiration Time');
        }

        return $this->setClaim(Enum::EXPIRATION_TIME, $timestamp);
    }

    /**
     * The "jti" (JWT ID) claim provides a unique identifier for the JWT.
     * The identifier value MUST be assigned in a manner that ensures that
     * there is a negligible probability that the same value will be
     * accidentally assigned to a different data object; if the application
     * uses multiple issuers, collisions MUST be prevented among values
     * produced by different issuers as well.  The "jti" claim can be used
     * to prevent the JWT from being replayed.  The "jti" value is a case-
     * sensitive string.  Use of this claim is OPTIONAL.
     *
     * @param string $jwtId
     *
     * @return Builder
     */
    public function setId(string $jwtId): Builder
    {
        return $this->setClaim(Enum::ID, $jwtId);
    }

    /**
     * The "iat" (issued at) claim identifies the time at which the JWT was
     * issued.  This claim can be used to determine the age of the JWT.  Its
     * value MUST be a number containing a NumericDate value.  Use of this
     * claim is OPTIONAL.
     *
     * @param int $timestamp
     *
     * @return Builder
     */
    public function setIssuedAt(int $timestamp): Builder
    {
        return $this->setClaim(Enum::ISSUED_AT, $timestamp);
    }

    /**
     * The "iss" (issuer) claim identifies the principal that issued the
     * JWT.  The processing of this claim is generally application specific.
     * The "iss" value is a case-sensitive string containing a StringOrURI
     * value.  Use of this claim is OPTIONAL.
     *
     * @param string $issuer
     *
     * @return Builder
     */
    public function setIssuer(string $issuer): Builder
    {
        return $this->setClaim(Enum::ISSUER, $issuer);
    }

    /**
     * The "nbf" (not before) claim identifies the time before which the JWT
     * MUST NOT be accepted for processing.  The processing of the "nbf"
     * claim requires that the current date/time MUST be after or equal to
     * the not-before date/time listed in the "nbf" claim.  Implementers MAY
     * provide for some small leeway, usually no more than a few minutes, to
     * account for clock skew.  Its value MUST be a number containing a
     * NumericDate value.  Use of this claim is OPTIONAL.
     *
     * @param int $timestamp
     *
     * @return Builder
     * @throws ValidatorException
     */
    public function setNotBefore(int $timestamp): Builder
    {
        if ($timestamp > time()) {
            throw new ValidatorException('Invalid Not Before');
        }

        return $this->setClaim(Enum::NOT_BEFORE, $timestamp);
    }

    /**
     * The "sub" (subject) claim identifies the principal that is the
     * subject of the JWT.  The claims in a JWT are normally statements
     * about the subject.  The subject value MUST either be scoped to be
     * locally unique in the context of the issuer or be globally unique.
     * The processing of this claim is generally application specific.  The
     * "sub" value is a case-sensitive string containing a StringOrURI
     * value.  Use of this claim is OPTIONAL.
     *
     * @param string $subject
     *
     * @return Builder
     */
    public function setSubject(string $subject): Builder
    {
        return $this->setClaim(Enum::SUBJECT, $subject);
    }

    /**
     * @param string $passphrase
     *
     * @return Builder
     * @throws ValidatorException
     */
    public function setPassphrase(string $passphrase): Builder
    {
        if (
            !preg_match(
                "/(?=^.{16,}$)((?=.*\d)|(?=.*\W+))(?![.\n])(?=.*[A-Z])(?=.*[a-z]).*$/",
                $passphrase
            )
        ) {
            throw new ValidatorException('Invalid passphrase (too weak)');
        }

        $this->passphrase = $passphrase;

        return $this;
    }

    /**
     * Sets a registered claim
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return Builder
     */
    private function setClaim(string $name, $value): Builder
    {
        $this->claims->set($name, $value);

        return $this;
    }
}
