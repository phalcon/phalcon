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
use Phalcon\Mvc\Model\Exceptions\ColumnNotInMap;
use Phalcon\Mvc\Model\Resultset;
use Phalcon\Support\Settings;

/**
 * @phpstan-import-type mvc_hydration_column_map from MvcTypes
 */
class CloneResultMapHydrate
{
    /**
     * Returns an hydrated result based on the data and the column map
     *
     * @param array  $data
     * @param mixed  $columnMap
     * @param int    $hydrationMode
     * @param string $calledClass
     *
     * @return array|mixed|object
     * @throws ColumnNotInMap
     *
     * @phpstan-param array<array-key, mixed> $data
     */
    public static function cloneResultMapHydrate(
        array $data,
        mixed $columnMap,
        int $hydrationMode,
        string $calledClass = "Phalcon\\Mvc\\Model"
    ) {
        /**
         * If there is no column map and the hydration mode is arrays return the
         * data as it is
         */
        if (!is_array($columnMap) && $hydrationMode == Resultset::HYDRATE_ARRAYS) {
            return $data;
        }

        /**
         * Create the destination object
         */
        $hydrateArray = [];

        foreach ($data as $key => $value) {
            if (!is_string($key)) {
                continue;
            }

            if (is_array($columnMap)) {
                // A column map value is an attribute name, or an array that
                // holds the attribute name in the first position.
                /** @var mvc_hydration_column_map $columnMap */

                // Try to find case-insensitive key variant
                if (
                    !isset($columnMap[$key]) &&
                    Settings::get("orm.case_insensitive_column_map")
                ) {
                    $key = CaseInsensitiveColumnMap::caseInsensitiveColumnMap($columnMap, $key);
                }

                /**
                 * Every field must be part of the column map
                 */
                if (!isset($columnMap[$key])) {
                    if (!Settings::get("orm.ignore_unknown_columns")) {
                        throw new ColumnNotInMap($key, $calledClass);
                    }

                    continue;
                } else {
                    $attribute = $columnMap[$key];
                }

                /**
                 * Attribute can store info about his type
                 */
                if (is_array($attribute)) {
                    $attributeName = $attribute[0];
                } else {
                    $attributeName = $attribute;
                }

                $hydrateArray[$attributeName] = $value;
            } else {
                $hydrateArray[$key] = $value;
            }
        }

        if ($hydrationMode != Resultset::HYDRATE_ARRAYS) {
            return (object)$hydrateArray;
        }

        return $hydrateArray;
    }
}
