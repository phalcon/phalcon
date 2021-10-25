<?php

/**
 * This file is part of the Phalcon.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Support\Helper\Str;

use function mb_convert_case;

use const MB_CASE_TITLE;

/**
 * Capitalizes the first letter of each word
 */
class Ucwords
{
    /**
     * @param string $text
     * @param string $encoding
     *
     * @return string
     */
    public function __invoke(
        string $text,
        string $encoding = 'UTF-8'
    ): string {
        return mb_convert_case($text, MB_CASE_TITLE, $encoding);
    }
}
