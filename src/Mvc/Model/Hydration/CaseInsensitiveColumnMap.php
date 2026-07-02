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

class CaseInsensitiveColumnMap
{
    /**
     * Attempts to find key case-insensitively
     *
     * @param array  $columnMap
     * @param string $key
     *
     * @return string
     */
    public static function caseInsensitiveColumnMap(
        array $columnMap,
        string $key
    ): string {
        $keys = array_keys($columnMap);
        foreach ($keys as $cmKey) {
            if (strtolower($cmKey) == strtolower($key)) {
                return $cmKey;
            }
        }

        return $key;
    }
}
