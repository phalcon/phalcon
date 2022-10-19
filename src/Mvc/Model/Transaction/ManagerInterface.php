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

namespace Phalcon\Mvc\Model\Transaction;

use Phalcon\Mvc\Model\TransactionInterface;

/**
 * Phalcon\Mvc\Model\Transaction\ManagerInterface
 *
 * Interface for Phalcon\Mvc\Model\Transaction\Manager
 */
interface ManagerInterface
{
    /**
     * Remove all the transactions from the manager
     *
     * @return void
     */
    public function collectTransactions(): void;

    /**
     * Commits active transactions within the manager
     *
     * @return mixed
     */
    public function commit();

    /**
     * Returns a new \Phalcon\Mvc\Model\Transaction or an already created once
     *
     * @param bool $autoBegin
     *
     * @return TransactionInterface
     */
    public function get(bool $autoBegin = true): TransactionInterface;

    /**
     * Returns the database service used to isolate the transaction
     *
     * @return string
     */
    public function getDbService(): string;

    /**
     * Check if the transaction manager is registering a shutdown function to
     * clean up pendent transactions
     *
     * @return bool
     */
    public function getRollbackPendent(): bool;

    /**
     * Checks whether manager has an active transaction
     *
     * @return bool
     */
    public function has(): bool;

    /**
     * Notifies the manager about a committed transaction
     *
     * @param TransactionInterface $transaction
     *
     * @return void
     */
    public function notifyCommit(TransactionInterface $transaction): void;

    /**
     * Notifies the manager about a rollbacked transaction
     *
     * @param TransactionInterface $transaction
     *
     * @return void
     */
    public function notifyRollback(TransactionInterface $transaction): void;

    /**
     * Rollbacks active transactions within the manager
     * Collect will remove transaction from the manager
     *
     * @param bool $collect
     *
     * @return void
     */
    public function rollback(bool $collect = false): void;

    /**
     * Rollbacks active transactions within the manager
     *
     * @return void
     */
    public function rollbackPendent(): void;

    /**
     * Sets the database service used to run the isolated transactions
     *
     * @param string $service
     *
     * @return ManagerInterface
     */
    public function setDbService(string $service): ManagerInterface;

    /**
     * Set if the transaction manager must register a shutdown function to clean up pendent transactions
     *
     * @param bool $rollbackPendent
     *
     * @return ManagerInterface
     */
    public function setRollbackPendent(bool $rollbackPendent): ManagerInterface;
}
