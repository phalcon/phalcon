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

namespace Phalcon\Contracts\Mvc;

use Phalcon\Contracts\Dispatcher\Dispatcher as DispatcherContract;
use Phalcon\Mvc\ControllerInterface;

/**
 * Canonical contract for Phalcon\Mvc\Dispatcher.
 */
interface Dispatcher extends DispatcherContract
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
     * @return DispatcherContract
     */
    public function setControllerName(string $controllerName): DispatcherContract;

    /**
     * Sets the default controller suffix
     *
     * @param string $controllerSuffix
     *
     * @return DispatcherContract
     */
    public function setControllerSuffix(string $controllerSuffix): DispatcherContract;

    /**
     * Sets the default controller name
     *
     * @param string $controllerName
     *
     * @return DispatcherContract
     */
    public function setDefaultController(string $controllerName): DispatcherContract;
}
