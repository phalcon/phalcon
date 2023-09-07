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

use function filter_var;

use const FILTER_SANITIZE_NUMBER_INT;

/**
 * Phalcon\Filter\Sanitize\IntVal
 *
 * Sanitizes a value to integer
 */
class IntVal
{
    /**
     * @param mixed $input The text to sanitize
     *
     * @return int
     */
    public function __invoke(mixed $input): int
    {
        return (int)filter_var($input, FILTER_SANITIZE_NUMBER_INT);
    }
}
