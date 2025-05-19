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

use InvalidArgumentException;
use Phalcon\Support\Helper\Json\Decode;
use Phalcon\Support\Traits\Base64Trait;

use function explode;
use function is_array;
use function json_decode;

/**
 * Token Parser class.
 *
 * It parses a token by validating if it is formed properly and splits it into
 * three parts. The headers are decoded, then the claims and finally the
 * signature. It returns a token object populated with the decoded information.
 */
class Parser
{
    use Base64Trait;

    /**
     * @var Decode
     */
    private Decode $decode;

    /**
     * @param Decode|null $decode
     */
    public function __construct(Decode | null $decode = null)
    {
        if (null === $decode) {
            $decode = new Decode();
        }

        $this->decode = $decode;
    }

    /**
     * Parse a token and return it
     *
     * @param string $token
     *
     * @return Token
     */
    public function parse(string $token): Token
    {
        $results          = $this->parseToken($token);
        $encodedHeaders   = $results[0];
        $encodedClaims    = $results[1];
        $encodedSignature = $results[2];
        $headers          = $this->decodeHeaders($encodedHeaders);
        $claims           = $this->decodeClaims($encodedClaims);
        $signature        = $this->decodeSignature($headers, $encodedSignature);

        return new Token($headers, $claims, $signature);
    }

    /**
     * Decode the claims
     *
     * @param string $claims
     *
     * @return Item
     */
    private function decodeClaims(string $claims): Item
    {
        $decoded = $this->decode->__invoke(
            $this->doDecodeUrl($claims),
            true
        );

        if (!is_array($decoded)) {
            throw new InvalidArgumentException(
                'Invalid Claims (not an array)'
            );
        }

        /**
         * Just in case
         */
        if (
            isset($decoded[Enum::AUDIENCE]) &&
            !is_array($decoded[Enum::AUDIENCE])
        ) {
            $decoded[Enum::AUDIENCE] = [$decoded[Enum::AUDIENCE]];
        }

        return new Item($decoded, $claims);
    }

    /**
     * Decodes the headers
     *
     * @param string $headers
     *
     * @return Item
     */
    private function decodeHeaders(string $headers): Item
    {
        $decoded = json_decode($this->doDecodeUrl($headers), true);

        if (!is_array($decoded)) {
            throw new InvalidArgumentException(
                'Invalid Header (not an array)'
            );
        }

        if (!isset($decoded[Enum::TYPE])) {
            throw new InvalidArgumentException(
                "Invalid Header (missing 'typ' element)"
            );
        }

        return new Item($decoded, $headers);
    }

    /**
     * Decodes the signature
     *
     * @param Item   $headers
     * @param string $signature
     *
     * @return Signature
     */
    private function decodeSignature(Item $headers, string $signature): Signature
    {
        $algo    = $headers->get(Enum::ALGO, 'none');
        $decoded = '';
        $sig     = '';
        if ('none' !== $algo) {
            $decoded = $this->doDecodeUrl($signature);
            $sig     = $signature;
        }

        return new Signature($decoded, $sig);
    }

    /**
     * Splits the token to its three parts;
     *
     * @param string $token
     *
     * @return array{0: string, 1: string, 2: string}
     */
    private function parseToken(string $token): array
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            throw new InvalidArgumentException(
                'Invalid JWT string (dots misalignment)'
            );
        }

        return $parts;
    }
}
