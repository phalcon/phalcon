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

namespace Phiz\Logger\Adapter;

use Phiz\Logger\Formatter\FormatterInterface;
use Phiz\Logger\Item;

/**
 * Phiz\Logger\AdapterInterface
 *
 * Interface for Phiz\Logger adapters
 */
interface AdapterInterface
{
    /**
     * Adds a message in the queue
     *
     * @param Item $item
     *
     * @return AdapterInterface
     */
    public function add(Item $item): AdapterInterface;

    /**
     * Starts a transaction
     *
     * @return AdapterInterface
     */
    public function begin(): AdapterInterface;

    /**
     * Closes the logger
     *
     * @return bool
     */
    public function close(): bool;

    /**
     * Commits the internal transaction
     *
     * @return AdapterInterface
     */
    public function commit(): AdapterInterface;

    /**
     * Returns the internal formatter
     *
     * @return FormatterInterface
     */
    public function getFormatter(): FormatterInterface;

    /**
     * Returns the whether the logger is currently in an active transaction or
     * not
     *
     * @return bool
     */
    public function inTransaction(): bool;

    /**
     * Processes the message in the adapter
     *
     * @param Item $item
     */
    public function process(Item $item): void;

    /**
     * Rollbacks the internal transaction
     *
     * @return AdapterInterface
     */
    public function rollback(): AdapterInterface;

    /**
     * Sets the message formatter
     *
     * @param FormatterInterface $formatter
     *
     * @return AdapterInterface
     */
    public function setFormatter(FormatterInterface $formatter): AdapterInterface;
}
