<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Implementation of this file has been influenced by sinbadxiii/cphalcon-auth
 * @link    https://github.com/sinbadxiii/cphalcon-auth
 */

declare(strict_types=1);

namespace Phalcon\Auth\Guard;

/**
 * Value object representing the contents of a remember-me cookie.
 *
 * @phpstan-type RememberPayload array{id?: int|string, token?: string, user_agent?: string}
 */
final class UserRemember
{
    protected int | string | null $id;

    protected string $token;

    protected string $userAgent;

    /**
     * Accepts either the raw JSON cookie value (string) or the already
     * decoded associative array. Malformed input degrades to an empty
     * payload so callers can read getters without null-guarding.
     *
     * @param string|array<string, mixed> $payload
     */
    public function __construct(string | array $payload)
    {
        $data = is_string($payload) ? json_decode($payload, true) : $payload;

        if (!is_array($data)) {
            $data = [];
        }

        /** @var RememberPayload $data */
        $rawId = $data['id'] ?? null;

        $this->id        = (is_int($rawId) || is_string($rawId)) ? $rawId : null;
        $this->token     = isset($data['token']) ? (string) $data['token'] : '';
        $this->userAgent = isset($data['user_agent']) ? (string) $data['user_agent'] : '';
    }

    public function getId(): int | string | null
    {
        return $this->id;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getUserAgent(): string
    {
        return $this->userAgent;
    }
}
