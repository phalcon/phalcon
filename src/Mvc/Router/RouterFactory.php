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

namespace Phalcon\Mvc\Router;

use Phalcon\Config\ConfigInterface;
use Phalcon\Mvc\Router;
use Phalcon\Mvc\Router\Exceptions\InvalidRouterFactoryConfig;
use Phalcon\Mvc\RouterInterface;

use function is_array;
use function is_object;

/**
 * Phalcon\Mvc\Router\RouterFactory
 *
 * Builds a Router from an array or ConfigInterface and loads routes via
 * Router::loadFromConfig.
 *
 * ```php
 * use Phalcon\Mvc\Router\RouterFactory;
 *
 * $router = (new RouterFactory())->load(
 *     [
 *         'defaultRoutes' => false,
 *         'routes' => [
 *             ['method' => 'get', 'pattern' => '/users', 'paths' => 'Users::index']
 *         ]
 *     ]
 * );
 * ```
 */
class RouterFactory
{
    /**
     * Builds a Router from a config array or ConfigInterface and loads routes.
     *
     * @param array|ConfigInterface $config
     *
     * @throws Exception
     *
     * @phpstan-param array<array-key, mixed>|ConfigInterface $config
     */
    public function load(mixed $config): RouterInterface
    {
        if (is_object($config)) {
            if (!($config instanceof ConfigInterface)) {
                throw new InvalidRouterFactoryConfig();
            }

            $config = $config->toArray();
        }

        if (!is_array($config)) {
            throw new InvalidRouterFactoryConfig();
        }

        $defaultRoutes = true;
        if (isset($config['defaultRoutes'])) {
            $defaultRoutes = (bool) $config['defaultRoutes'];
        }

        $router = $this->newInstance($defaultRoutes);
        $router->loadFromConfig($config);

        return $router;
    }

    /**
     * Returns a bare Router instance.
     */
    public function newInstance(bool $defaultRoutes = true): RouterInterface
    {
        return new Router($defaultRoutes);
    }
}
