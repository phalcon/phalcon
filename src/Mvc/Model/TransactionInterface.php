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

namespace Phalcon\Mvc\Model;

use Phalcon\Db\Adapter\AdapterInterface;
use Phalcon\Mvc\Model\Transaction\ManagerInterface;
use Phalcon\Mvc\ModelInterface;

/**
 * Interface for Phalcon\Mvc\Model\Transaction
 */
interface TransactionInterface
{
    /**
     * Starts the transaction
     */
    public function begin(): bool;

    /**
     * Commits the transaction
     */
    public function commit(): bool;

    /**
     * Returns connection related to transaction
     */
    public function getConnection(): AdapterInterface;

    /**
     * Returns validations messages from last save try
     *
     * @phpstan-return array<array-key, mixed>
     */
    public function getMessages(): array;

    /**
     * Checks whether transaction is managed by a transaction manager
     */
    public function isManaged(): bool;

    /**
     * Checks whether internal connection is under an active transaction
     */
    public function isValid(): bool;

    /**
     * Rollbacks the transaction
     */
    public function rollback(
        string | null $rollbackMessage = null,
        ModelInterface | null $rollbackRecord = null
    ): bool;

    /**
     * Sets if is a reused transaction or new once
     */
    public function setIsNewTransaction(bool $isNew): void;

    /**
     * Sets object which generates rollback action
     */
    public function setRollbackedRecord(ModelInterface $record): void;

    /**
     * Sets flag to rollback on abort the HTTP connection
     */
    public function setRollbackOnAbort(bool $rollbackOnAbort): void;

    /**
     * Sets transaction manager related to the transaction
     */
    public function setTransactionManager(ManagerInterface $manager): void;

    /**
     * Enables throwing exception
     */
    public function throwRollbackException(bool $status): TransactionInterface;
}
