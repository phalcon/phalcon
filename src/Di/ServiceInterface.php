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

namespace Phalcon\Di;

/**
 * Represents a service in the services container
 */
interface ServiceInterface
{
    /**
     * Returns the service definition
     *
     * @return mixed
     */
    public function getDefinition();

    /**
     * Returns a parameter in a specific position
     *
     * @param int $position
     *
     * @return mixed
     */
    public function getParameter(int $position);

    /**
     * Returns true if the service was resolved
     *
     * @return bool
     */
    public function isResolved(): bool;

    /**
     * Check whether the service is shared or not
     *
     * @return bool
     */
    public function isShared(): bool;

    /**
     * Resolves the service
     *
     * @param array parameters
     *
     * @param array|null       $parameters
     * @param DiInterface|null $container
     *
     * @return mixed
     */
    public function resolve(array $parameters = null, DiInterface $container = null);

    /**
     * Set the service definition
     *
     * @param mixed $definition
     *
     * @return mixed
     */
    public function setDefinition($definition);

    /**
     * Changes a parameter in the definition without resolve the service
     *
     * @param int   $position
     * @param array $parameter
     *
     * @return ServiceInterface
     */
    public function setParameter(int $position, array $parameter): ServiceInterface;

    /**
     * Sets if the service is shared or not
     *
     * @param bool $shared
     *
     * @return mixed
     */
    public function setShared(bool $shared);
}
