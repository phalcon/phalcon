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
use Phalcon\Di\DiInterface;
use Phalcon\Mvc\Model\Transaction\Failed as TxFailed;
use Phalcon\Mvc\Model\Transaction\ManagerInterface;
use Phalcon\Mvc\ModelInterface;

/**
 * Transactions are protective blocks where SQL statements are only permanent if
 * they can all succeed as one atomic action. Phalcon\Transaction is intended to
 * be used with Phalcon_Model_Base. Phalcon Transactions should be created using
 * Phalcon\Transaction\Manager.
 *
 * ```php
 * use Phalcon\Mvc\Model\Transaction\Failed;
 * use Phalcon\Mvc\Model\Transaction\Manager;
 *
 * try {
 *     $manager = new Manager();
 *
 *     $transaction = $manager->get();
 *
 *     $robot = new Robots();
 *
 *     $robot->setTransaction($transaction);
 *
 *     $robot->name       = "WALL·E";
 *     $robot->created_at = date("Y-m-d");
 *
 *     if ($robot->save() === false) {
 *         $transaction->rollback("Can't save robot");
 *     }
 *
 *     $robotPart = new RobotParts();
 *
 *     $robotPart->setTransaction($transaction);
 *
 *     $robotPart->type = "head";
 *
 *     if ($robotPart->save() === false) {
 *         $transaction->rollback("Can't save robot part");
 *     }
 *
 *     $transaction->commit();
 * } catch(Failed $e) {
 *     echo "Failed, reason: ", $e->getMessage();
 * }
 * ```
 */
class Transaction implements TransactionInterface
{
    /**
     * @var bool
     */
    protected bool $activeTransaction = false;

    /**
     * @var AdapterInterface
     */
    protected AdapterInterface $connection;

    /**
     * @var bool
     */
    protected bool $isNewTransaction = true;

    /**
     * @var ManagerInterface|null
     */
    protected ManagerInterface | null $manager = null;

    /**
     * @var array
     */
    protected array $messages = [];
    /**
     * @var bool
     */
    protected bool $rollbackOnAbort = false;
    /**
     * @var ModelInterface|null
     */
    protected ModelInterface | null $rollbackRecord = null;
    /**
     * @var bool
     */
    protected bool $rollbackThrowException = false;

    /**
     * Phalcon\Mvc\Model\Transaction constructor
     *
     * @param DiInterface $container
     * @param bool        $autoBegin
     * @param string      $service
     */
    public function __construct(
        DiInterface $container,
        bool $autoBegin = false,
        string $service = "db"
    ) {
        $connection = $container->get($service);

        $this->connection = $connection;

        if (true === $autoBegin) {
            $connection->begin();
        }
    }

    /**
     * Starts the transaction
     *
     * @return bool
     */
    public function begin(): bool
    {
        return $this->connection->begin();
    }

    /**
     * Commits the transaction
     *
     * @return bool
     */
    public function commit(): bool
    {
        if (null !== $this->manager) {
            $this->manager->notifyCommit($this);
        }

        return $this->connection->commit();
    }

    /**
     * Returns the connection related to transaction
     *
     * @return AdapterInterface
     */
    public function getConnection(): AdapterInterface
    {
        if (
            true === $this->rollbackOnAbort &&
            true === connection_aborted()
        ) {
            $this->rollback("The request was aborted");
        }

        return $this->connection;
    }

    /**
     * Returns validations messages from last save try
     *
     * @return array
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * Checks whether transaction is managed by a transaction manager
     *
     * @return bool
     */
    public function isManaged(): bool
    {
        return null !== $this->manager;
    }

    /**
     * Checks whether internal connection is under an active transaction
     *
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->connection->isUnderTransaction();
    }

    /**
     * Rollbacks the transaction
     */
    public function rollback(
        string | null $rollbackMessage = null,
        ModelInterface | null $rollbackRecord = null
    ): bool {
        if (null !== $this->manager) {
            $this->manager->notifyRollback($this);
        }

        if ($this->connection->rollback()) {
            if (!$rollbackMessage) {
                $rollbackMessage = "Transaction aborted";
            }

            if (null !== $rollbackRecord) {
                $this->rollbackRecord = $rollbackRecord;
            }

            if ($this->rollbackThrowException) {
                throw new TxFailed($rollbackMessage, $this->rollbackRecord);
            }
        }

        return true;
    }

    /**
     * Sets if is a reused transaction or new once
     *
     * @param bool $isNew
     *
     * @return void
     */
    public function setIsNewTransaction(bool $isNew): void
    {
        $this->isNewTransaction = $isNew;
    }

    /**
     * Sets flag to rollback on abort the HTTP connection
     *
     * @param bool $rollbackOnAbort
     *
     * @return void
     */
    public function setRollbackOnAbort(bool $rollbackOnAbort): void
    {
        $this->rollbackOnAbort = $rollbackOnAbort;
    }

    /**
     * Sets object which generates rollback action
     *
     * @param ModelInterface $record
     *
     * @return void
     */
    public function setRollbackedRecord(ModelInterface $record): void
    {
        $this->rollbackRecord = $record;
    }

    /**
     * Sets transaction manager related to the transaction
     *
     * @param ManagerInterface $manager
     *
     * @return void
     */
    public function setTransactionManager(ManagerInterface $manager): void
    {
        $this->manager = $manager;
    }

    /**
     * Enables throwing exception
     */
    public function throwRollbackException(bool $status): TransactionInterface
    {
        $this->rollbackThrowException = $status;

        return $this;
    }
}
