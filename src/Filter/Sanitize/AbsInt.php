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

use function abs;
use function filter_var;
use function intval;

use const FILTER_SANITIZE_NUMBER_INT;

/**
 * Phalcon\Filter\Sanitize\AbsInt
 *
 * Sanitizes a value to absolute integer
 */
class AbsInt
{
    /**
     * @param mixed $input The text to sanitize
     *
     * @return int
     */
    public function __invoke(mixed $input): int
    {
        return (int) abs(
            intval(
                filter_var($input, FILTER_SANITIZE_NUMBER_INT)
            )
        );
    }
}
