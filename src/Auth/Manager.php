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

namespace Phalcon\Auth;

use Phalcon\Auth\Access\AccessLocator;
use Phalcon\Contracts\Auth\Access\Access;
use Phalcon\Contracts\Auth\Adapter\Adapter;
use Phalcon\Contracts\Auth\AuthUser;
use Phalcon\Contracts\Auth\Guard\Guard;
use Phalcon\Contracts\Auth\Manager as ManagerContract;

/**
 * Composes guards (authentication) and access gates (authorization)
 * behind a single facade. Calls to undefined methods are forwarded to
 * the default guard via __call; the methods listed below cover the
 * Session/Token guard surface so callers and static analyzers can rely
 * on them.
 *
 * @phpstan-import-type AuthCredentials from Adapter
 *
 * @method bool           basic(string $field = 'email', array<string, mixed> $extraConditions = [])
 * @method string         getName()
 * @method string         getRememberName()
 * @method ?string        getTokenForRequest()
 * @method bool           hasUser()
 * @method ?AuthUser      getLastUserAttempted()
 * @method false|AuthUser loginById(int|string $id, bool $remember = false)
 * @method bool           once(array<string, mixed> $credentials = [])
 * @method false|AuthUser onceBasic(string $field = 'email', array<string, mixed> $extraConditions = [])
 * @method bool           viaRemember()
 */
class Manager implements ManagerContract
{
    /**
     * @var array<string, class-string<Access>>
     */
    protected array $accessList = [];

    protected ?Access $activeAccess = null;

    protected ?Guard $defaultGuard = null;

    /**
     * @var array<string, Guard>
     */
    protected array $guards = [];

    public function __construct(
        protected AccessLocator $accessFactory
    ) {
    }

    /**
     * @param list<mixed> $params
     */
    public function __call(string $method, array $params): mixed
    {
        $guard = $this->guard();

        /** @var callable $callable */
        $callable = [$guard, $method];

        return call_user_func_array($callable, $params);
    }

    /**
     * @throws Exception
     */
    public function access(string $accessName): self
    {
        if (!isset($this->accessList[$accessName])) {
            throw new Exception(
                sprintf("Access '%s' is not registered", $accessName)
            );
        }

        $this->activeAccess = $this->accessFactory->newInstance($accessName, [$this]);

        return $this;
    }

    /**
     * @param array<string, class-string<Access>> $accessList
     */
    public function addAccessList(array $accessList): self
    {
        $this->accessList = array_merge($this->accessList, $accessList);
        $this->registerAccessList($accessList);

        return $this;
    }

    public function addGuard(
        string $nameGuard,
        Guard $guard,
        bool $isDefault = false
    ): self {
        $this->guards[$nameGuard] = $guard;

        if ($isDefault) {
            $this->defaultGuard = $guard;
        }

        return $this;
    }

    /**
     * @phpstan-param AuthCredentials $credentials
     *
     * @throws Exception
     */
    public function attempt(array $credentials = [], bool $remember = false): bool
    {
        $guard = $this->guard();

        if (!method_exists($guard, 'attempt')) {
            throw new Exception('Default guard does not support attempt()');
        }

        return (bool) $guard->attempt($credentials, $remember);
    }

    public function check(): bool
    {
        return $this->guard()->check();
    }

    /**
     * @throws Exception
     */
    public function except(string ...$actions): self
    {
        if ($this->activeAccess === null) {
            throw new Exception('No active access — call access() first');
        }

        $this->activeAccess->setExceptActions(array_values($actions));

        return $this;
    }

    public function getAccess(): ?Access
    {
        return $this->activeAccess;
    }

    /**
     * @return array<string, class-string<Access>>
     */
    public function getAccessList(): array
    {
        return $this->accessList;
    }

    public function getDefaultGuard(): ?Guard
    {
        return $this->defaultGuard;
    }

    /**
     * @return array<string, Guard>
     */
    public function getGuards(): array
    {
        return $this->guards;
    }

    /**
     * @throws Exception
     */
    public function guard(?string $name = null): Guard
    {
        if ($name === null) {
            if ($this->defaultGuard === null) {
                throw new Exception('No default guard registered');
            }

            return $this->defaultGuard;
        }

        if (!isset($this->guards[$name])) {
            throw new Exception(
                sprintf("Auth guard '%s' is not defined", $name)
            );
        }

        return $this->guards[$name];
    }

    public function id(): int | string | null
    {
        return $this->guard()->id();
    }

    public function logout(): void
    {
        $guard = $this->guard();

        if (method_exists($guard, 'logout')) {
            $guard->logout();
        }
    }

    /**
     * @throws Exception
     */
    public function only(string ...$actions): self
    {
        if ($this->activeAccess === null) {
            throw new Exception('No active access — call access() first');
        }

        $this->activeAccess->setOnlyActions(array_values($actions));

        return $this;
    }

    public function setAccess(Access $access): self
    {
        $this->activeAccess = $access;

        return $this;
    }

    /**
     * @param array<string, class-string<Access>> $accessList
     */
    public function setAccessList(array $accessList): self
    {
        $this->accessList = $accessList;
        $this->registerAccessList($accessList);

        return $this;
    }

    public function setDefaultGuard(Guard $guard): self
    {
        $this->defaultGuard = $guard;

        return $this;
    }

    public function user(): ?AuthUser
    {
        return $this->guard()->user();
    }

    /**
     * @phpstan-param AuthCredentials $credentials
     */
    public function validate(array $credentials = []): bool
    {
        return $this->guard()->validate($credentials);
    }

    /**
     * Mirrors the access list into the locator so a single name → class
     * lookup applies in both places.
     *
     * @param array<string, class-string<Access>> $accessList
     */
    protected function registerAccessList(array $accessList): void
    {
        foreach ($accessList as $name => $className) {
            $this->accessFactory->register($name, $className);
        }
    }
}
