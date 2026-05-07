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

use Phalcon\Auth\Exception;
use Phalcon\Auth\Guard\Config\SessionGuardConfig;
use Phalcon\Auth\Internal\Options;
use Phalcon\Container\Service\Collection;
use Phalcon\Contracts\Auth\Adapter\Adapter;
use Phalcon\Contracts\Auth\Adapter\RememberAdapter;
use Phalcon\Contracts\Auth\AuthRemember;
use Phalcon\Contracts\Auth\AuthUser;
use Phalcon\Contracts\Auth\Guard\BasicAuth;
use Phalcon\Contracts\Auth\Guard\GuardStateful;
use Phalcon\Contracts\Auth\RememberToken;
use Phalcon\Http\RequestInterface;
use Phalcon\Http\Response\CookiesInterface;
use Phalcon\Session\ManagerInterface as SessionManagerInterface;
use Phalcon\Support\Helper\Json\Encode;

/**
 * @phpstan-import-type AuthCredentials from Adapter
 *
 * @extends AbstractGuard<SessionGuardConfig>
 */
class Session extends AbstractGuard implements GuardStateful, BasicAuth
{
    protected bool $viaRemember = false;

    public function __construct(
        Adapter $adapter,
        protected RequestInterface $request,
        protected CookiesInterface $cookies,
        protected SessionManagerInterface $session,
        SessionGuardConfig $config = new SessionGuardConfig(),
    ) {
        parent::__construct($adapter, $config);
    }

    public static function fromOptions(
        Adapter $adapter,
        Collection $container,
        array $options
    ): static {
        $config = new SessionGuardConfig(
            Options::stringOrNull($options, 'suffix'),
            Options::stringOrNull($options, 'name'),
            Options::stringOrNull($options, 'rememberName'),
        );

        return new static(
            $adapter,
            Options::resolveService($container, RequestInterface::class, 'Session guard'),
            Options::resolveService($container, CookiesInterface::class, 'Session guard'),
            Options::resolveService($container, SessionManagerInterface::class, 'Session guard'),
            $config,
        );
    }

    /**
     * @phpstan-param AuthCredentials $credentials
     *
     * @throws Exception
     */
    public function attempt(array $credentials = [], bool $remember = false): bool
    {
        $resolved                = $this->adapter->retrieveByCredentials($credentials);
        $this->lastUserAttempted = $resolved;

        if ($this->hasValidCredentials($resolved, $credentials)) {
            $this->login($resolved, $remember);

            return true;
        }

        return false;
    }

    /**
     * @param array<string, mixed> $extraConditions
     *
     * @throws Exception
     */
    public function basic(string $field = 'email', array $extraConditions = []): bool
    {
        if ($this->check()) {
            return true;
        }

        return $this->attemptBasic($field, $extraConditions);
    }

    public function getName(): string
    {
        return $this->config->getName();
    }

    public function getRememberName(): string
    {
        return $this->config->getRememberName();
    }

    /**
     * @throws Exception
     */
    public function login(AuthUser $user, bool $remember = false): void
    {
        $this->fireManagerEvent('auth:beforeLogin');

        $this->session->set($this->getName(), $user->getAuthIdentifier());

        if ($remember) {
            if (!($this->adapter instanceof RememberAdapter)) {
                throw Exception::doesNotImplement('Adapter', 'RememberAdapter');
            }
            $this->rememberUser($user);
        }

        $this->setUser($user);

        $this->fireManagerEvent('auth:afterLogin');
    }

    /**
     * @throws Exception
     */
    public function loginById(int | string $id, bool $remember = false): false | AuthUser
    {
        $resolved = $this->adapter->retrieveById($id);
        if ($resolved === null) {
            return false;
        }

        $this->login($resolved, $remember);

        return $resolved;
    }

    public function logout(): void
    {
        $current = $this->user();

        $this->fireManagerEvent('auth:beforeLogout', ['user' => $current]);

        $recaller = $this->recaller();
        if ($recaller !== null && $current instanceof AuthRemember) {
            $token    = $recaller->getToken();
            $tokenRow = $current->getRememberToken($token);
            $tokenRow?->delete();

            if ($this->cookies->has($this->getRememberName())) {
                $this->cookies->delete($this->getRememberName());
            }
        }

        $this->session->remove($this->getName());

        $this->fireManagerEvent('auth:afterLogout', ['user' => $current]);

        $this->user = null;
    }

    /**
     * @phpstan-param AuthCredentials $credentials
     */
    public function once(array $credentials = []): bool
    {
        $this->fireManagerEvent('auth:beforeLogin');

        if ($this->validate($credentials)) {
            $this->setUser($this->lastUserAttempted);
            $this->fireManagerEvent('auth:afterLogin');

            return true;
        }

        return false;
    }

    /**
     * @param array<string, mixed> $extraConditions
     */
    public function onceBasic(
        string $field = 'email',
        array $extraConditions = []
    ): false | AuthUser {
        $credentials = $this->basicCredentials($field);
        if ($credentials === null) {
            return false;
        }

        if ($this->once(array_merge($credentials, $extraConditions))) {
            /** @var AuthUser $user (non-null after successful once()) */
            $user = $this->user;

            return $user;
        }

        return false;
    }

    public function user(): ?AuthUser
    {
        if ($this->user !== null) {
            return $this->user;
        }

        $id = $this->session->get($this->getName());
        if (is_int($id) || is_string($id)) {
            $this->user = $this->adapter->retrieveById($id);
        }

        $recaller = $this->recaller();
        if ($this->user === null && $recaller !== null) {
            $fromRecaller = $this->userFromRecaller($recaller);
            if ($fromRecaller !== null) {
                $this->user = $fromRecaller;
                $this->session->set($this->getName(), $fromRecaller->getAuthIdentifier());
            }
        }

        return $this->user;
    }

    /**
     * @phpstan-param AuthCredentials $credentials
     *
     * @phpstan-assert-if-true !null $this->lastUserAttempted
     */
    public function validate(array $credentials = []): bool
    {
        $resolved                = $this->adapter->retrieveByCredentials($credentials);
        $this->lastUserAttempted = $resolved;

        return $this->hasValidCredentials($resolved, $credentials);
    }

    public function viaRemember(): bool
    {
        return $this->viaRemember;
    }

    /**
     * @param array<string, mixed> $extraConditions
     *
     * @throws Exception
     */
    protected function attemptBasic(string $field, array $extraConditions = []): bool
    {
        $credentials = $this->basicCredentials($field);
        if ($credentials === null) {
            return false;
        }

        return $this->attempt(array_merge($credentials, $extraConditions));
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function basicCredentials(string $field): ?array
    {
        $basic = $this->request->getBasicAuth();
        if ($basic === null) {
            return null;
        }

        return [
            $field     => $basic['username'],
            'password' => $basic['password'],
        ];
    }

    protected function createRememberToken(AuthUser $user): RememberToken
    {
        /** @var RememberAdapter $adapter */
        $adapter = $this->adapter;

        return $adapter->createRememberToken($user);
    }

    protected function recaller(): ?UserRemember
    {
        if (!$this->cookies->has($this->getRememberName())) {
            return null;
        }

        $raw = $this->cookies->get($this->getRememberName())->getValue();
        if (empty($raw)) {
            return null;
        }

        if (is_string($raw)) {
            return new UserRemember($raw);
        }

        if (!is_array($raw)) {
            return null;
        }

        /** @var array<string, mixed> $raw */
        return new UserRemember($raw);
    }

    protected function rememberUser(AuthUser $user): void
    {
        $token = $this->createRememberToken($user);

        $agent   = (string) $this->request->getUserAgent();
        $payload = (new Encode())->__invoke(
            [
                'id'         => $user->getAuthIdentifier(),
                'token'      => $token->getToken(),
                'user_agent' => $agent,
            ],
            JSON_THROW_ON_ERROR
        );

        $this->cookies->set(
            $this->getRememberName(),
            $payload,
            time() + 360 * 24 * 60 * 60
        );
    }

    protected function userFromRecaller(UserRemember $recaller): ?AuthUser
    {
        if (!($this->adapter instanceof RememberAdapter)) {
            return null;
        }

        $id = $recaller->getId();
        if ($id === null) {
            return null;
        }

        $resolved = $this->adapter->retrieveByToken(
            $id,
            $recaller->getToken(),
            $recaller->getUserAgent()
        );

        $this->viaRemember = $resolved !== null;

        return $resolved;
    }
}
