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

use function htmlspecialchars;

use const FILTER_SANITIZE_STRING;

/**
 * Phalcon\Filter\Sanitize\String
 *
 * Sanitizes a value to string
 */
class StringVal
{
    /**
     * @param string $input The text to sanitize
     *
     * @return string
     */
    public function __invoke(string $input): string
    {
        return htmlspecialchars($input);
    }
}
