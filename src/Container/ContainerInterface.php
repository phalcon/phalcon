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

/**
 * PSR-11 Wrapper for `Phalcon\Di`
 */
interface ContainerInterface
{
    /**
     * Return the service
     *
     * @param string $id
     *
     * @return mixed
     */
    public function get(string $id);

    /**
     * Whether a service exists or not in the container
     *
     * @param string $id
     *
     * @return bool
     */
    public function has(string $id): bool;
}
