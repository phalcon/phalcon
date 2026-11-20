<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Mvc\Model\Hydration;

use Phalcon\Support\Traits\WeakTypesTrait;

class CaseInsensitiveColumnMap
{
    use WeakTypesTrait;

    /**
     * Attempts to find key case-insensitively
     */
    public static function caseInsensitiveColumnMap(
        mixed $columnMap,
        mixed $key
    ): string {
        // The column map is an array. Its keys are the column names.
        // The key is a column name.
        /**
         * @var array<array-key, mixed> $columnMap
         * @var string                  $key
         * @var list<string>            $keys
         */
        $keys = array_keys($columnMap);
        foreach ($keys as $cmKey) {
            if (
                self::weakCallArray('strtolower', [$cmKey])
                == self::weakCallArray('strtolower', [$key])
            ) {
                return self::weakReturnString($cmKey);
            }
        }

        return self::weakReturnString($key);
    }
}
