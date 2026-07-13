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

namespace Phalcon\Mvc\Model\Query;

use Phalcon\Messages\MessageInterface;
use Phalcon\Mvc\ModelInterface;

/**
 * This class represents the status returned by a PHQL
 * statement like INSERT, UPDATE or DELETE. It offers context
 * information and the related messages produced by the
 * model which finally executes the operations when it fails
 *
 *```php
 * $phql = "UPDATE Invoices
 *      SET inv_title = :inv_title:,
 *          inv_status_flag = :inv_status_flag:,
 *          inv_total = :inv_total:
 *      WHERE inv_id = :inv_id:";
 *
 * $status = $app->modelsManager->executeQuery(
 *     $phql,
 *     [
 *         "inv_id"          => 100,
 *         "inv_title"       => "Test Invoice",
 *         "inv_status_flag" => 1,
 *         "inv_total"       => 1959,
 *     ]
 * );
 *
 * // Check if the update was successful
 * if ($status->success()) {
 *     echo "OK";
 * }
 *```
 */
class Status implements StatusInterface
{
    /**
     * Phalcon\Mvc\Model\Query\Status
     *
     * @param bool                $success
     * @param ModelInterface|null $model
     */
    public function __construct(
        protected bool $success,
        protected ModelInterface | null $model = null
    ) {
    }

    /**
     * Returns the messages produced because of a failed operation
     *
     * @return MessageInterface[]
     */
    public function getMessages(): array
    {
        if (null === $this->model) {
            return [];
        }

        return $this->model->getMessages();
    }

    /**
     * Returns the model that executed the action
     *
     * @return ModelInterface|null
     */
    public function getModel(): ?ModelInterface
    {
        return $this->model;
    }

    /**
     * Allows to check if the executed operation was successful
     *
     * @return bool
     */
    public function success(): bool
    {
        return $this->success;
    }
}
