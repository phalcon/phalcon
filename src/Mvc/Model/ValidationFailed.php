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

use Phalcon\Mvc\ModelInterface;

/**
 * Phalcon\Mvc\Model\ValidationFailed
 *
 * This exception is generated when a model fails to save a record
 * Phalcon\Mvc\Model must be set up to have this behavior
 */
class ValidationFailed extends Exception
{
    /**
     * Phalcon\Mvc\Model\ValidationFailed constructor
     *
     * @param ModelInterface $model
     * @param array          $validationMessages
     */
    public function __construct(
        protected ModelInterface $model,
        protected array $validationMessages
    ) {
        $messageStr = "Validation failed";
        if (!empty($validationMessages)) {
            /**
             * Get the first message in the array
             */
            $message = $validationMessages[0];

            /**
             * Get the message to use it in the exception
             */
            $messageStr = $message->getMessage();
        }

        parent::__construct($messageStr);
    }

    /**
     * Returns the complete group of messages produced in the validation
     *
     * @return array
     */
    public function getMessages(): array
    {
        return $this->validationMessages;
    }

    /**
     * Returns the model that generated the messages
     *
     * @return ModelInterface
     */
    public function getModel(): ModelInterface
    {
        return $this->model;
    }
}
