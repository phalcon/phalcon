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

namespace Phalcon\Container;

use Phalcon\Di\DiInterface;

/**
 * Wrapper for `Phalcon\Di\Di`
 *
 * A full lazy loading autoloader will be implemented
 */
class Container implements ContainerInterface
{
    /**
     * @var DiInterface
     */
    protected DiInterface $container;

    /**
     * Phalcon\Container constructor
     *
     * @param DiInterface $container
     */
    public function __construct(DiInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Return the service
     *
     * @param string $id
     *
     * @return mixed
     */
    public function get(string $id)
    {
        return $this->container->getShared($id);
    }

    /**
     * Whether a service exists or not in the container
     *
     * @param string $id
     *
     * @return bool
     */
    public function has(string $id): bool
    {
        return $this->container->has($id);
    }
}
