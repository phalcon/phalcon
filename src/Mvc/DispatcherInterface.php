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

namespace Phalcon\Mvc;

use Phalcon\Dispatcher\DispatcherInterface as DispatcherInterfaceBase;

/**
 * Interface for Phalcon\Mvc\Dispatcher
 */
interface DispatcherInterface extends DispatcherInterfaceBase
{
    /**
     * Returns the active controller in the dispatcher
     *
     * @return ControllerInterface|null
     */
    public function getActiveController(): ControllerInterface | null;

    /**
     * Gets last dispatched controller name
     *
     * @return string
     */
    public function getControllerName(): string;

    /**
     * Returns the latest dispatched controller
     *
     * @return ControllerInterface|null
     */
    public function getLastController(): ControllerInterface | null;

    /**
     * Sets the controller name to be dispatched
     *
     * @param string $controllerName
     *
     * @return DispatcherInterfaceBase
     */
    public function setControllerName(string $controllerName): DispatcherInterfaceBase;

    /**
     * Sets the default controller suffix
     *
     * @param string $controllerSuffix
     *
     * @return DispatcherInterfaceBase
     */
    public function setControllerSuffix(string $controllerSuffix): DispatcherInterfaceBase;

    /**
     * Sets the default controller name
     *
     * @param string $controllerName
     *
     * @return DispatcherInterfaceBase
     */
    public function setDefaultController(string $controllerName): DispatcherInterfaceBase;
}
