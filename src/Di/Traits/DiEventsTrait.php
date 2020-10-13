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

namespace Phalcon\Di\Traits;

use Phalcon\Events\ManagerInterface;

/**
 * Trait DiEventsTrait
 *
 * @package Phalcon\Di\Traits
 */
trait DiEventsTrait
{
    /**
     * @param ManagerInterface|null $eventsManager
     * @param string                $name
     * @param array|null            $parameters
     * @param mixed                 $instance
     *
     * @return mixed
     */
    private function fireAfterServiceResolve(
        $eventsManager,
        string $name,
        array $parameters = null,
        $instance = null
    ) {
        /**
         * Allows for custom creation of instances through the
         * "di:afterServiceResolve" event.
         */
        if (null !== $eventsManager) {
            $instance = $eventsManager->fire(
                'di:afterServiceResolve',
                $this,
                [
                    'name'       => $name,
                    'parameters' => $parameters,
                    'instance'   => $instance,
                ]
            );
        }

        return $instance;
    }

    /**
     * @param ManagerInterface|null $eventsManager
     * @param string                $name
     * @param array|null            $parameters
     * @param mixed                 $instance
     *
     * @return mixed
     */
    private function fireBeforeServiceResolve(
        $eventsManager,
        string $name,
        array $parameters = null,
        $instance = null
    ) {
        /**
         * Allows for custom creation of instances through the
         * "di:beforeServiceResolve" event.
         */
        if (null !== $eventsManager) {
            $instance = $eventsManager->fire(
                'di:beforeServiceResolve',
                $this,
                [
                    'name'       => $name,
                    'parameters' => $parameters,
                ]
            );
        }

        return $instance;
    }
}
