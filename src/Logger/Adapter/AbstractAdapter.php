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

namespace Phalcon\Logger\Adapter;

use Phalcon\Logger\Exception;
use Phalcon\Logger\Formatter\FormatterInterface;
use Phalcon\Logger\Formatter\Line;
use Phalcon\Logger\Item;

/**
 * Class AbstractAdapter
 *
 * @property string             $defaultFormatter
 * @property FormatterInterface $formatter
 * @property bool               $inTransaction
 * @property array              $queue
 */
abstract class AbstractAdapter implements AdapterInterface
{
    /**
     * Name of the default formatter class
     *
     * @var string
     */
    protected string $defaultFormatter = Line::class;

    /**
     * Formatter
     *
     * @var FormatterInterface|null
     */
    protected ?FormatterInterface $formatter = null;

    /**
     * Tells if there is an active transaction or not
     *
     * @var bool
     */
    protected bool $inTransaction = false;

    /**
     * Array with messages queued in the transaction
     *
     * @var array
     */
    protected array $queue = [];

    /**
     * Destructor cleanup
     *
     * @throws Exception
     */
    public function __destruct()
    {
        if ($this->inTransaction) {
            $this->commit();
        }

        $this->close();
    }

    /**
     * Adds a message to the queue
     */
    /**
     * @param Item $item
     *
     * @return AdapterInterface
     */
    public function add(Item $item): AdapterInterface
    {
        $this->queue[] = $item;

        return $this;
    }

    /**
     * Starts a transaction
     */
    public function begin(): AdapterInterface
    {
        $this->inTransaction = true;

        return $this;
    }

    /**
     * Commits the internal transaction
     *
     * @return AdapterInterface
     * @throws Exception
     */
    public function commit(): AdapterInterface
    {
        $this->checkTransaction();

        /**
         * Check if the queue has something to log
         */
        foreach ($this->queue as $item) {
            $this->process($item);
        }

        // Clear logger queue at commit
        $this->resetTransaction();

        return $this;
    }

    /**
     * @return FormatterInterface
     */
    public function getFormatter(): FormatterInterface
    {
        if (null === $this->formatter) {
            $className = $this->defaultFormatter;

            $this->formatter = new $className();
        }

        return $this->formatter;
    }

    /**
     * Returns the whether the logger is currently in an active transaction or
     * not
     */
    public function inTransaction(): bool
    {
        return $this->inTransaction;
    }

    /**
     * Processes the message in the adapter
     *
     * @param Item $item
     */
    abstract public function process(Item $item): void;

    /**
     * Rollbacks the internal transaction
     *
     * @return AdapterInterface
     * @throws Exception
     */
    public function rollback(): AdapterInterface
    {
        $this->checkTransaction();
        $this->resetTransaction();

        return $this;
    }

    /**
     * Sets the message formatter
     */
    /**
     * @param FormatterInterface $formatter
     *
     * @return AdapterInterface
     */
    public function setFormatter(FormatterInterface $formatter): AdapterInterface
    {
        $this->formatter = $formatter;

        return $this;
    }

    /**
     * Checks if the transaction is active
     *
     * @throws Exception
     */
    private function checkTransaction(): void
    {
        if (true !== $this->inTransaction) {
            throw new Exception('There is no active transaction');
        }
    }

    /**
     * Resets the transaction flag and queue array
     */
    private function resetTransaction(): void
    {
        $this->queue         = [];
        $this->inTransaction = false;
    }
}
