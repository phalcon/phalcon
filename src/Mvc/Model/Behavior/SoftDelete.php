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

namespace Phalcon\Mvc\Model\Behavior;

use Phalcon\Mvc\Model\AbstractBehavior;
use Phalcon\Mvc\Model\Exception;
use Phalcon\Mvc\ModelInterface;
use Phalcon\Parsers\Parser;

/**
 * Instead of permanently delete a record it marks the record as deleted
 * changing the value of a flag column
 */
class SoftDelete extends AbstractBehavior
{
    /**
     * Listens for notifications from the models manager
     */
    public function notify(string $type, ModelInterface $model)
    {
        if ('beforeDelete' !== $type) {
            return;
        }

        $options = $this->getOptions();

        /**
         * 'value' is the value to be updated instead of delete the record
         */
        if (true !== isset($options['value'])) {
            throw new Exception("The option 'value' is required");
        }

        /**
         * 'field' is the attribute to be updated instead of delete the record
         */
        if (true !== isset($options['field'])) {
            throw new Exception("The option 'field' is required");
        }

        $field = $options['field'];
        $value = $options['value'];

        /**
         * Skip the current operation
         */
        $model->skipOperation(true);

        /**
         * If the record is already flagged as 'deleted' we don't delete it again
         */
        if ($value === $model->readAttribute($field)) {
            return;
        }

        $modelsManager = $model->getModelsManager();

        /**
         * Clone the current model to make a clean new operation
         */
        $updateModel = clone $model;

        $updateModel->writeAttribute($field, $value);

        /**
         * Update the cloned model
         */
        if (true !== $updateModel->save()) {
            /**
             * Transfer the messages from the cloned model to the original model
             */
            foreach ($updateModel->getMessages() as $message) {
                $model->appendMessage($message);
            }

            return false;
        }

        /**
         * Update the original model too
         */
        $model->writeAttribute($field, $value);

        if (
            true === $modelsManager->isKeepingSnapshots($model) &&
            Parser::settingGet("orm.update_snapshot_on_save")
        ) {
            $metaData = $model->getModelsMetaData();

            $model->setSnapshotData($updateModel->getSnapshotData());

            $model->setOldSnapshotData($updateModel->getOldSnapshotData());
        }
    }
}
