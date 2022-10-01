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

namespace Phalcon\Filter\Sanitize;

use function in_array;

/**
 * Phalcon\Filter\Sanitize\BoolVal
 *
 * Sanitizes a value to boolean
 */
class BoolVal
{
    /**
     * @param mixed $input The text to sanitize
     *
     * @return bool
     */
    public function __invoke(mixed $input): bool
    {
        $trueArray  = ['true', 'on', 'yes', 'y', '1'];
        $falseArray = ['false', 'off', 'no', 'n', '0'];

        if (true === in_array($input, $trueArray)) {
            return true;
        }

        if (true === in_array($input, $falseArray)) {
            return false;
        }

        return (bool) $input;
    }
}
