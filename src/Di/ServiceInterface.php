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

use Phalcon\Contracts\Di\DiTypes;

/**
 * Represents a service in the services container
 *
 * @phpstan-import-type di_parameters from DiTypes
 * @phpstan-import-type di_service_argument from DiTypes
 */
interface ServiceInterface
{
    /**
     * Returns the service definition
     */
    public function getDefinition(): mixed;

    /**
     * Returns a parameter in a specific position
     */
    public function getParameter(int $position): mixed;

    /**
     * Returns true if the service was resolved
     */
    public function isResolved(): bool;

    /**
     * Check whether the service is shared or not
     */
    public function isShared(): bool;

    /**
     * Resolves the service
     *
     * @phpstan-param di_parameters|null $parameters
     */
    public function resolve(
        array | null $parameters = null,
        DiInterface | null $container = null
    ): mixed;

    /**
     * Set the service definition
     *
     * @return mixed
     */
    public function setDefinition(mixed $definition);

    /**
     * Changes a parameter in the definition without resolve the service
     *
     * @phpstan-param di_service_argument $parameter
     */
    public function setParameter(int $position, array $parameter): ServiceInterface;

    /**
     * Sets if the service is shared or not
     *
     * @return mixed
     */
    public function setShared(bool $shared);
}
