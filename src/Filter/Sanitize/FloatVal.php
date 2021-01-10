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

namespace Phiz\Filter\Sanitize;

use function filter_var;

use const FILTER_FLAG_ALLOW_FRACTION;
use const FILTER_SANITIZE_NUMBER_FLOAT;

/**
 * Phiz\Filter\Sanitize\FloatVal
 *
 * Sanitizes a value to float
 */
class FloatVal
{
    /**
     * @param mixed $input The text to sanitize
     *
     * @return float
     */
    public function __invoke($input)
    {
        return (double) filter_var(
            $input,
            FILTER_SANITIZE_NUMBER_FLOAT,
            [
                'flags' => FILTER_FLAG_ALLOW_FRACTION
            ]
        );
    }
}
