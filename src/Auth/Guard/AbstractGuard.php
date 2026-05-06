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

use Phalcon\Contracts\Auth\Adapter\Adapter;
use Phalcon\Contracts\Auth\AuthUser;
use Phalcon\Contracts\Auth\Guard\Guard;
use Phalcon\Contracts\Auth\Guard\GuardConfig;
use Phalcon\Events\Traits\EventsAwareTrait;

/**
 * @phpstan-import-type AuthCredentials from Adapter
 *
 * @template TConfig of GuardConfig
 */
abstract class AbstractGuard implements Guard
{
    use EventsAwareTrait;

    protected ?AuthUser $lastUserAttempted = null;

    protected ?AuthUser $user = null;

    /**
     * @phpstan-param TConfig $config
     */
    public function __construct(
        protected Adapter $adapter,
        protected GuardConfig $config,
    ) {
    }

    /**
     * Returns the guard configuration object.
     *
     * @phpstan-return TConfig
     */
    public function getConfig(): GuardConfig
    {
        return $this->config;
    }

    public function check(): bool
    {
        return $this->user() !== null;
    }

    public function getAdapter(): Adapter
    {
        return $this->adapter;
    }

    public function getLastUserAttempted(): ?AuthUser
    {
        return $this->lastUserAttempted;
    }

    public function guest(): bool
    {
        return !$this->check();
    }

    public function hasUser(): bool
    {
        return $this->user !== null;
    }

    public function id(): int | string | null
    {
        $current = $this->user();

        if ($current === null) {
            return null;
        }

        return $current->getAuthIdentifier();
    }

    public function setAdapter(Adapter $adapter): static
    {
        $this->adapter = $adapter;

        return $this;
    }

    public function setUser(AuthUser $user): static
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @phpstan-param AuthCredentials $credentials
     *
     * @phpstan-assert-if-true !null $user
     */
    protected function hasValidCredentials(?AuthUser $user, array $credentials): bool
    {
        if ($user === null) {
            return false;
        }

        return $this->adapter->validateCredentials($user, $credentials);
    }
}
