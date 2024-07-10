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
 * Interface for Phalcon\Mvc\Model\Query\Status
 */
interface StatusInterface
{
    /**
     * Returns the messages produced by an operation failed
     *
     * @return MessageInterface[]
     */
    public function getMessages(): array;

    /**
     * Returns the model which executed the action
     *
     * @return ModelInterface | null
     */
    public function getModel(): ModelInterface | null;

    /**
     * Allows to check if the executed operation was successful
     *
     * @return bool
     */
    public function success(): bool;
}
