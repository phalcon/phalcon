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

namespace Phalcon\Mvc\Model\Hydration;

use Phalcon\Contracts\Mvc\MvcTypes;
use Phalcon\Mvc\Model\Exception;
use Phalcon\Mvc\Model\Exceptions\InvalidDumpResultKey;
use Phalcon\Mvc\ModelInterface;

/**
 * @phpstan-import-type mvc_model_data from MvcTypes
 */
class CloneResult
{
    /**
     * Assigns values to a model from an array returning a new model
     *
     *```php
     * $invoice = Phalcon\Mvc\Model::cloneResult(
     *     new Invoices(),
     *     [
     *         "type" => "mechanical",
     *         "name" => "Test Invoice",
     *         "inv_total" => 100,
     *     ]
     * );
     *```
     *
     * @throws Exception
     *
     * @phpstan-param mvc_model_data $data
     */
    public static function cloneResult(
        ModelInterface $base,
        array $data,
        int $dirtyState = 0
    ): ModelInterface {
        /**
         * Clone the base record
         */
        $instance = clone $base;

        /**
         * Declared private properties must be written via reflection during
         * hydration - see Hydration\GetPrivateProperties
         */
        $privateProperties = GetPrivateProperties::getPrivateProperties(get_class($instance));

        /**
         * Mark the object as persistent
         */
        $instance->setDirtyState($dirtyState);

        foreach ($data as $key => $value) {
            if (!is_string($key)) {
                throw new InvalidDumpResultKey(get_class($base));
            }

            if (isset($privateProperties[$key])) {
                $privateProperties[$key]->setValue($instance, $value);
            } else {
                $instance->$key = $value;
            }
        }

        /**
         * Call afterFetch, this allows the developer to execute actions after a
         * record is fetched from the database
         */
        $instance->fireEvent("afterFetch");

        return $instance;
    }
}
