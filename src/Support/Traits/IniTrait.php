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

namespace Phalcon\Support\Traits;

use function ini_get;
use function strtolower;

trait IniTrait
{
    /**
     * Query a php.ini value and return it back as boolean
     *
     * @param string $input
     * @param bool   $defaultValue
     *
     * @return bool
     */
    private function iniGetBool(string $input, bool $defaultValue = false): bool
    {
        $value = ini_get($input);
        if (false === $value) {
            return $defaultValue;
        }

        $value = match (strtolower($value)) {
            'true',
            'on',
            'yes',
            'y',
            '1'     => true,
            default => false,
        };

        return $value;
    }
}
