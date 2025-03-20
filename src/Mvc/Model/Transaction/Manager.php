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

use Phalcon\Di\Di;
use Phalcon\Di\DiInterface;
use Phalcon\Di\InjectionAwareInterface;
use Phalcon\Mvc\Model\Transaction;
use Phalcon\Mvc\Model\TransactionInterface;

use function array_reverse;
use function is_object;

/**
 * A transaction acts on a single database connection. If you have multiple
 * class-specific databases, the transaction will not protect interaction among
 * them.
 *
 * This class manages the objects that compose a transaction.
 * A transaction produces a unique connection that is passed to every object
 * part of the transaction.
 *
 * ```php
 * use Phalcon\Mvc\Model\Transaction\Failed;
 * use Phalcon\Mvc\Model\Transaction\Manager;
 *
 * try {
 *    $transactionManager = new Manager();
 *
 *    $transaction = $transactionManager->get();
 *
 *    $robot = new Robots();
 *
 *    $robot->setTransaction($transaction);
 *
 *    $robot->name       = "WALL·E";
 *    $robot->created_at = date("Y-m-d");
 *
 *    if ($robot->save() === false) {
 *        $transaction->rollback("Can't save robot");
 *    }
 *
 *    $robotPart = new RobotParts();
 *
 *    $robotPart->setTransaction($transaction);
 *
 *    $robotPart->type = "head";
 *
 *    if ($robotPart->save() === false) {
 *        $transaction->rollback("Can't save robot part");
 *    }
 *
 *    $transaction->commit();
 * } catch (Failed $e) {
 *    echo "Failed, reason: ", $e->getMessage();
 * }
 *```
 */
class Manager implements ManagerInterface, InjectionAwareInterface
{
    /**
     * @var bool
     */
    protected bool $initialized = false;

    /**
     * @var int
     */
    protected int $number = 0;

    /**
     * @var bool
     */
    protected bool $rollbackPendent = true;

    /**
     * @var string
     */
    protected string $service = "db";

    /**
     * @var array
     */
    protected array $transactions = [];

    /**
     * Phalcon\Mvc\Model\Transaction\Manager constructor
     *
     * @param DiInterface|null $container
     *
     * @throws Exception
     */
    public function __construct(
        protected DiInterface | null $container = null
    ) {
        if (null === $container) {
            $container = Di::getDefault();
        }

        $this->container = $container;

        if (!is_object($container)) {
            throw new Exception(
                "A dependency injection container is required "
                . "to access the services related to the ORM"
            );
        }
    }

    /**
     * Remove all the transactions from the manager
     *
     * @return void
     */
    public function collectTransactions(): void
    {
        $transactions = $this->transactions;

        foreach ($this->transactions as $transaction) {
            $this->number--;
        }

        $this->transactions = [];
    }

    /**
     * Commits active transactions within the manager
     *
     * @return void
     */
    public function commit(): void
    {
        foreach ($this->transactions as $transaction) {
            $connection = $transaction->getConnection();

            if (true === $connection->isUnderTransaction()) {
                $connection->commit();
            }
        }
    }

    /**
     * Returns a new \Phalcon\Mvc\Model\Transaction or an already created once
     * This method registers a shutdown function to rollback active connections
     *
     * @param bool $autoBegin
     *
     * @return TransactionInterface
     */
    public function get(bool $autoBegin = true): TransactionInterface
    {
        if (!$this->initialized) {
            if ($this->rollbackPendent) {
                register_shutdown_function(
                    [
                        $this,
                        "rollbackPendent",
                    ]
                );
            }

            $this->initialized = true;
        }

        return $this->getOrCreateTransaction($autoBegin);
    }

    /**
     * Returns the dependency injection container
     *
     * @return DiInterface|null
     */
    public function getDI(): DiInterface | null
    {
        return $this->container;
    }

    /**
     * Returns the database service used to isolate the transaction
     *
     * @return string
     */
    public function getDbService(): string
    {
        return $this->service;
    }

    /**
     * Create/Returns a new transaction or an existing one
     *
     * @param bool $autoBegin
     *
     * @return TransactionInterface
     * @throws Exception
     */
    public function getOrCreateTransaction(bool $autoBegin = true): TransactionInterface
    {
        if (null === $this->container) {
            throw new Exception(
                "A dependency injection container is required "
                . "to access the services related to the ORM"
            );
        }

        if ($this->number) {
            $reverseTransactions = array_reverse($this->transactions);

            foreach ($reverseTransactions as $transaction) {
                if (is_object($transaction)) {
                    $transaction->setIsNewTransaction(false);

                    return $transaction;
                }
            }
        }

        $transaction = new Transaction(
            $this->container,
            $autoBegin,
            $this->service
        );
        $transaction->setTransactionManager($this);

        $this->transactions[] = $transaction;
        $this->number++;

        return $transaction;
    }

    /**
     * Check if the transaction manager is registering a shutdown function to
     * clean up pendent transactions
     *
     * @return bool
     */
    public function getRollbackPendent(): bool
    {
        return $this->rollbackPendent;
    }

    /**
     * Checks whether the manager has an active transaction
     *
     * @return bool
     */
    public function has(): bool
    {
        return $this->number > 0;
    }

    /**
     * Notifies the manager about a committed transaction
     *
     * @param TransactionInterface $transaction
     *
     * @return void
     */
    public function notifyCommit(TransactionInterface $transaction): void
    {
        $this->collectTransaction($transaction);
    }

    /**
     * Notifies the manager about a rollbacked transaction
     *
     * @param TransactionInterface $transaction
     *
     * @return void
     */
    public function notifyRollback(TransactionInterface $transaction): void
    {
        $this->collectTransaction($transaction);
    }

    /**
     * Rollbacks active transactions within the manager
     * Collect will remove the transaction from the manager
     *
     * @param bool $collect
     *
     * @return void
     */
    public function rollback(bool $collect = true): void
    {
        foreach ($this->transactions as $transaction) {
            $connection = $transaction->getConnection();

            if ($connection->isUnderTransaction()) {
                $connection->rollback();
                $connection->close();
            }

            if (true === $collect) {
                $this->collectTransaction($transaction);
            }
        }
    }

    /**
     * Rollbacks active transactions within the manager
     *
     * @return void
     */
    public function rollbackPendent(): void
    {
        $this->rollback();
    }

    /**
     * Sets the dependency injection container
     *
     * @param DiInterface $container
     *
     * @return void
     */
    public function setDI(DiInterface $container): void
    {
        $this->container = $container;
    }

    /**
     * Sets the database service used to run the isolated transactions
     *
     * @param string $service
     *
     * @return ManagerInterface
     */
    public function setDbService(string $service): ManagerInterface
    {
        $this->service = $service;

        return $this;
    }

    /**
     * Set if the transaction manager must register a shutdown function to clean
     * up pendent transactions
     *
     * @param bool $rollbackPendent
     *
     * @return ManagerInterface
     */
    public function setRollbackPendent(bool $rollbackPendent): ManagerInterface
    {
        $this->rollbackPendent = $rollbackPendent;

        return $this;
    }

    /**
     * Removes transactions from the TransactionManager
     *
     * @param TransactionInterface $transaction
     *
     * @return void
     */
    protected function collectTransaction(TransactionInterface $transaction): void
    {
        $newTransactions = [];

        foreach ($this->transactions as $managedTransaction) {
            if ($managedTransaction != $transaction) {
                $newTransactions[] = $transaction;
            } else {
                $this->number--;
            }
        }

        $this->transactions = $newTransactions;
    }
}
