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

use Phalcon\Messages\MessageInterface;
use Phalcon\Mvc\ModelInterface;

/**
 * Phalcon\Mvc\Model\Transaction\Failed
 *
 * This class will be thrown to exit a try/catch block for isolated transactions
 */
class Failed extends Exception
{
    /**
     * Constructor
     *
     * @param string              $message
     * @param ModelInterface|null $record
     */
    public function __construct(
        string $message,
        protected ModelInterface | null $record = null
    ) {
        $this->record = $record;

        parent::__construct($message);
    }

    /**
     * Returns validation record messages which stop the transaction
     *
     * @return ModelInterface|null
     */
    public function getRecord(): ModelInterface | null
    {
        return $this->record;
    }

    /**
     * Returns validation record messages which stop the transaction
     *
     * @return MessageInterface[]|string
     */
    public function getRecordMessages(): array | string
    {
        if (null !== $this->record) {
            return $this->record->getMessages();
        }

        return $this->getMessage();
    }
}
