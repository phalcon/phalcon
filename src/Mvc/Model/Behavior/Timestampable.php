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

use Closure;
use Phalcon\Mvc\Model\AbstractBehavior;
use Phalcon\Mvc\Model\Exception;
use Phalcon\Mvc\ModelInterface;

use function is_array;
use function is_object;

/**
 * Allows to automatically update a model’s attribute saving the datetime when a
 * record is created or updated
 */
class Timestampable extends AbstractBehavior
{
    /**
     * Listens for notifications from the models manager
     *
     * @param string         $type
     * @param ModelInterface $model
     *
     * @return void|null
     * @throws Exception
     */
    public function notify(string $type, ModelInterface $model)
    {
        /**
         * Check if the developer decided to take action here
         */
        if (true !== $this->mustTakeAction($type)) {
            return null;
        }

        $options = $this->getOptions($type);

        if (true === empty($options)) {
            return;
        }

        /**
         * The field name is required in this behavior
         */
        if (true !== isset($options['field'])) {
            throw new Exception("The option 'field' is required");
        }

        $field     = $options['field'];
        $timestamp = $this->getTimestamp($options);

        /**
         * Assign the value to the field, use writeAttribute() if the property
         * is protected
         */
        if (true === is_array($field)) {
            foreach ($field as $singleField) {
                $model->writeAttribute($singleField, $timestamp);
            }
        } else {
            $model->writeAttribute($field, $timestamp);
        }
    }

    /**
     * @param array $options
     *
     * @return string
     */
    private function getTimestamp(array $options)
    {
        if (true === isset($options['format'])) {
            /**
             * Format is a format for date()
             */
            return date($options['format']);
        }

        if (true === isset($options["generator"])) {
            $generator = $options["generator"];
            /**
             * A generator is a closure that produce the correct timestamp value
             */
            if (
                true === is_object($generator) &&
                $generator instanceof Closure
            ) {
                return call_user_func($generator);
            }
        }

        /**
         * Last resort call time()
         */
        return time();
    }
}
