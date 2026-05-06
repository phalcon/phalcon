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

namespace Phalcon\Contracts\Auth\Guard;

use Phalcon\Contracts\Auth\Adapter\Adapter;
use Phalcon\Contracts\Auth\AuthUser;

/**
 * @phpstan-import-type AuthCredentials from Adapter
 */
interface Guard
{
    /**
     * Whether the current request is authenticated.
     */
    public function check(): bool;

    /**
     * Whether the current request is unauthenticated.
     */
    public function guest(): bool;

    /**
     * Returns the authenticated user's identifier, or null when no
     * authenticated user is present.
     */
    public function id(): int | string | null;

    /**
     * Sets the current user explicitly. Returns $this for fluent chaining.
     */
    public function setUser(AuthUser $user): static;

    /**
     * Returns the resolved user for the current request, or null.
     */
    public function user(): ?AuthUser;

    /**
     * Validates the given credentials without logging in.
     *
     * @phpstan-param AuthCredentials $credentials
     */
    public function validate(array $credentials = []): bool;
}
