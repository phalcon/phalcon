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

use Phalcon\Contracts\Auth\AuthUser;

/**
 * Implemented by guards backed by persistent state (sessions/cookies).
 */
interface GuardStateful
{
    public function login(AuthUser $user, bool $remember = false): void;

    /**
     * Logs in the user identified by $id. Returns the resolved user on
     * success or false when no user matches the id.
     */
    public function loginById(int | string $id, bool $remember = false): false | AuthUser;

    public function logout(): void;

    public function viaRemember(): bool;
}
