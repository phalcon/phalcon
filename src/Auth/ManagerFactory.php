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
use Phalcon\Auth\Adapter\Config\MemoryAdapterConfig;
use Phalcon\Auth\Adapter\Config\ModelAdapterConfig;
use Phalcon\Auth\Adapter\Config\StreamAdapterConfig;
use Phalcon\Auth\Adapter\Memory;
use Phalcon\Auth\Adapter\Model;
use Phalcon\Auth\Adapter\Stream;
use Phalcon\Auth\Guard\Config\TokenGuardConfig;
use Phalcon\Auth\Guard\Session;
use Phalcon\Auth\Guard\Token;
use Phalcon\Config\ConfigInterface;
use Phalcon\Container\Service\Collection;
use Phalcon\Contracts\Auth\Access\Access;
use Phalcon\Contracts\Auth\Adapter\Adapter;
use Phalcon\Contracts\Auth\Guard\Guard;
use Phalcon\Encryption\Security;
use Phalcon\Http\RequestInterface;
use Phalcon\Http\Response\CookiesInterface;
use Phalcon\Session\ManagerInterface as SessionManagerInterface;

/**
 * Single entry-point factory that builds a fully wired Phalcon\Auth\Manager
 * from a config tree. Framework-shared services (RequestInterface,
 * CookiesInterface, SessionManagerInterface) are resolved from the injected
 * container so the manager wires against the real application singletons,
 * not separately constructed copies.
 *
 *  [
 *      'guards' => [
 *          'web' => [
 *              'type'    => 'session',
 *              'default' => true,
 *              'adapter' => [
 *                  'name'    => 'model',
 *                  'options' => [
 *                      'model' => User::class
 *                  ],
 *              ],
 *              'options' => [],
 *          ],
 *          'api' => [
 *              'type'    => 'token',
 *              'adapter' => [
 *                  'name'    => 'model',
 *                  'options' => [
 *                      'model' => User::class
 *                  ]
 *              ],
 *              'options' => [
 *                  'inputKey'   => 'api_token',
 *                  'storageKey' => 'api_token'
 *              ],
 *          ],
 *      ],
 *      'access' => [
 *          'auth'  => \Phalcon\Auth\Access\Auth::class,
 *          'guest' => \Phalcon\Auth\Access\Guest::class,
 *      ],
 *  ]
 *
 * @phpstan-type GuardConfig array{
 *     type: string,
 *     default?: bool,
 *     adapter: array{name: string, options?: array<string, mixed>},
 *     options?: array<string, mixed>,
 * }
 *
 * @phpstan-type AuthConfig array{
 *     guards?: array<string, GuardConfig>,
 *     access?: array<string, class-string<Access>>,
 * }
 */
class ManagerFactory
{
    public function __construct(
        protected readonly Security $hasher,
        protected readonly Collection $container,
    ) {
    }

    /**
     * @phpstan-param AuthConfig|ConfigInterface $config
     *
     * @throws Exception
     */
    public function load(array | ConfigInterface $config): Manager
    {
        if ($config instanceof ConfigInterface) {
            /** @var AuthConfig $config */
            $config = $config->toArray();
        }

        $manager = new Manager(new AccessLocator($this->container));

        /** @var array<string, GuardConfig> $guards */
        $guards = $config['guards'] ?? [];

        foreach ($guards as $name => $gconf) {
            $adapter = $this->buildAdapter($gconf['adapter']);
            $guard   = $this->buildGuard(
                $gconf['type'],
                $adapter,
                $gconf['options'] ?? []
            );

            $manager->addGuard(
                (string) $name,
                $guard,
                (bool) ($gconf['default'] ?? false)
            );
        }

        $accessList = $config['access'] ?? [];
        if (!empty($accessList)) {
            $manager->setAccessList($accessList);
        }

        return $manager;
    }

    /**
     * @param array{name: string, options?: array<string, mixed>} $cfg
     *
     * @throws Exception
     */
    protected function buildAdapter(array $cfg): Adapter
    {
        $options = $cfg['options'] ?? [];

        return match ($cfg['name']) {
            'memory' => new Memory(
                $this->hasher,
                new MemoryAdapterConfig(
                    $this->arrayOption($options, 'users', []),
                    $this->stringOrNull($options, 'model')
                )
            ),
            'model'  => new Model(
                $this->hasher,
                new ModelAdapterConfig(
                    $this->requireString($options, 'model', 'model adapter')
                )
            ),
            'stream' => new Stream(
                $this->hasher,
                new StreamAdapterConfig(
                    $this->requireString($options, 'file', 'stream adapter'),
                    $this->stringOrNull($options, 'model')
                )
            ),
            default  => throw new Exception(
                sprintf("Unknown auth adapter '%s'", $cfg['name'])
            ),
        };
    }

    /**
     * @param array<string, mixed> $options
     *
     * @throws Exception
     */
    protected function buildGuard(string $type, Adapter $adapter, array $options): Guard
    {
        return match ($type) {
            'session' => $this->buildSession($adapter),
            'token'   => $this->buildToken($adapter, $options),
            default   => throw new Exception(
                sprintf("Unknown auth guard '%s'", $type)
            ),
        };
    }

    /**
     * @throws Exception
     */
    protected function buildSession(Adapter $adapter): Session
    {
        return new Session(
            $adapter,
            $this->getService(RequestInterface::class, 'Session guard'),
            $this->getService(CookiesInterface::class, 'Session guard'),
            $this->getService(SessionManagerInterface::class, 'Session guard'),
        );
    }

    /**
     * @param array<string, mixed> $options
     *
     * @throws Exception
     */
    protected function buildToken(Adapter $adapter, array $options): Token
    {
        return new Token(
            $adapter,
            $this->getService(RequestInterface::class, 'Token guard'),
            new TokenGuardConfig(
                $this->requireString($options, 'inputKey', 'token guard'),
                $this->requireString($options, 'storageKey', 'token guard')
            )
        );
    }

    /**
     * @param array<string, mixed>                              $options
     * @param list<array{id?: int|string}&array<string, mixed>> $default
     *
     * @return list<array{id?: int|string}&array<string, mixed>>
     */
    private function arrayOption(array $options, string $key, array $default): array
    {
        $value = $options[$key] ?? $default;

        if (!is_array($value)) {
            return $default;
        }

        /** @var list<array{id?: int|string}&array<string, mixed>> */
        return array_values($value);
    }

    /**
     * @template T of object
     *
     * @phpstan-param class-string<T> $serviceId
     *
     * @phpstan-return T
     *
     * @throws Exception
     */
    private function getService(string $serviceId, string $context): object
    {
        if (!$this->container->has($serviceId)) {
            throw new Exception(
                sprintf(
                    "Auth %s requires service '%s' to be bound in the container",
                    $context,
                    $serviceId
                )
            );
        }

        /** @var T */
        return $this->container->get($serviceId);
    }

    /**
     * @param array<string, mixed> $options
     *
     * @throws Exception
     */
    private function requireString(array $options, string $key, string $context): string
    {
        $value = $options[$key] ?? null;

        if (!is_string($value) || $value === '') {
            throw new Exception(
                sprintf("Auth %s requires '%s' to be a non-empty string", $context, $key)
            );
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $options
     */
    private function stringOrNull(array $options, string $key): ?string
    {
        $value = $options[$key] ?? null;

        return is_string($value) ? $value : null;
    }
}
