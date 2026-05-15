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
use Phalcon\Mvc\RouterInterface;

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
     * @return RouterInterface
     * @throws Exception
     */
    public function load(array | ConfigInterface $config): RouterInterface
    {
        if ($config instanceof ConfigInterface) {
            $config = $config->toArray();
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
     *
     * @param bool $defaultRoutes
     *
     * @return RouterInterface
     */
    public function newInstance(bool $defaultRoutes = true): RouterInterface
    {
        return new Router($defaultRoutes);
    }
}
