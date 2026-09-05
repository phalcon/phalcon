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

namespace Phalcon\Contracts\Logger\Adapter;

use Phalcon\Logger\Formatter\FormatterInterface;
use Phalcon\Logger\Item;

/**
 * Canonical contract for Phalcon\Logger adapters.
 */
interface Adapter
{
    /**
     * Adds a message in the queue
     */
    public function add(Item $item): Adapter;

    /**
     * Starts a transaction
     */
    public function begin(): Adapter;

    /**
     * Closes the logger
     */
    public function close(): bool;

    /**
     * Commits the internal transaction
     */
    public function commit(): Adapter;

    /**
     * Returns the internal formatter
     */
    public function getFormatter(): FormatterInterface;

    /**
     * Returns the whether the logger is currently in an active transaction or
     * not
     */
    public function inTransaction(): bool;

    /**
     * Processes the message in the adapter
     */
    public function process(Item $item): void;

    /**
     * Rollbacks the internal transaction
     */
    public function rollback(): Adapter;

    /**
     * Sets the message formatter
     */
    public function setFormatter(FormatterInterface $formatter): Adapter;
}
