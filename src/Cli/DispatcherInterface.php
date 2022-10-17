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

namespace Phalcon\Cli;

use Phalcon\Dispatcher\DispatcherInterface as DispatcherInterfaceBase;

/**
 * Interface for Phalcon\Cli\Dispatcher
 */
interface DispatcherInterface extends DispatcherInterfaceBase
{
    /**
     * Returns the active task in the dispatcher
     *
     * @return TaskInterface|null
     */
    public function getActiveTask(): ?TaskInterface;

    /**
     * Returns the latest dispatched controller
     *
     * @return TaskInterface|null
     */
    public function getLastTask(): ?TaskInterface;

    /**
     * Get dispatched options
     *
     * @return array
     */
    public function getOptions(): array;

    /**
     * Gets last dispatched task name
     *
     * @return string
     */
    public function getTaskName(): string;

    /**
     * Gets default task suffix
     *
     * @return string
     */
    public function getTaskSuffix(): string;

    /**
     * Sets the default task name
     *
     * @param string $taskName
     *
     * @return void
     */
    public function setDefaultTask(string $taskName): void;

    /**
     * Set the options to be dispatched
     *
     * @param array $options
     *
     * @return void
     */
    public function setOptions(array $options): void;

    /**
     * Sets the task name to be dispatched
     *
     * @param string $taskName
     *
     * @return void
     */
    public function setTaskName(string $taskName): void;

    /**
     * Sets the default task suffix
     *
     * @param string $taskSuffix
     *
     * @return void
     */
    public function setTaskSuffix(string $taskSuffix): void;
}
