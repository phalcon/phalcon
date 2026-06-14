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
use Phalcon\Auth\Adapter\AdapterLocator;
use Phalcon\Auth\Guard\GuardLocator;
use Phalcon\Auth\Internal\Options;
use Phalcon\Config\ConfigInterface;
use Phalcon\Contracts\Auth\Access\Access;
use Phalcon\Contracts\Auth\Adapter\Adapter;
use Phalcon\Contracts\Auth\Guard\Guard;
use Phalcon\Contracts\Container\Service\Collection;
use Phalcon\Di\DiInterface;
use Phalcon\Encryption\Security;

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
    protected readonly AccessLocator $accessLocator;

    protected readonly AdapterLocator $adapterLocator;

    protected readonly GuardLocator $guardLocator;

    public function __construct(
        protected readonly Security $hasher,
        protected readonly Collection | DiInterface $container,
        ?AdapterLocator $adapterLocator = null,
        ?GuardLocator $guardLocator = null,
        ?AccessLocator $accessLocator = null,
    ) {
        $this->adapterLocator = $adapterLocator ?? new AdapterLocator($container);
        $this->guardLocator   = $guardLocator   ?? new GuardLocator($container);
        $this->accessLocator  = $accessLocator  ?? new AccessLocator($container);
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

        $manager = new Manager($this->accessLocator);

        /** @var array<string, GuardConfig> $guards */
        $guards = $config['guards'] ?? [];

        foreach ($guards as $name => $gconf) {
            $adapter = $this->buildAdapter(
                $this->adapterLocator,
                Options::requireArray($gconf, 'adapter', "guard '" . $name . "'")
            );
            $guard   = $this->buildGuard(
                $this->guardLocator,
                Options::requireString($gconf, 'type', "guard '" . $name . "'"),
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
            $manager->addAccessList($accessList);
        }

        return $manager;
    }

    /**
     * @param array{name: string, options?: array<string, mixed>} $cfg
     *
     * @throws Exception
     */
    protected function buildAdapter(AdapterLocator $locator, array $cfg): Adapter
    {
        $name = Options::requireString($cfg, 'name', 'adapter');

        if (!$locator->has($name)) {
            throw new Exception(sprintf("Unknown auth adapter '%s'", $name));
        }

        return $locator->getClass($name)::fromOptions(
            $this->hasher,
            $cfg['options'] ?? []
        );
    }

    /**
     * @param array<string, mixed> $options
     *
     * @throws Exception
     */
    protected function buildGuard(
        GuardLocator $locator,
        string $type,
        Adapter $adapter,
        array $options
    ): Guard {
        if (!$locator->has($type)) {
            throw new Exception(sprintf("Unknown auth guard '%s'", $type));
        }

        return $locator->getClass($type)::fromOptions(
            $adapter,
            $this->container,
            $options
        );
    }
}
