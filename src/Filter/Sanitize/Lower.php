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

use function mb_convert_case;

/**
 * Phalcon\Filter\Sanitize\Lower
 *
 * Sanitizes a value to lowercase
 */
class Lower
{
    /**
     * @param string $input The text to sanitize
     *
     * @return string
     */
    public function __invoke(string $input): string
    {
        return mb_convert_case($input, MB_CASE_LOWER, "UTF-8");
    }
}
