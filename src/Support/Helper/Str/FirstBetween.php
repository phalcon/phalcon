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

use function trim;

/**
 * Returns the first string there is between the strings from the
 * parameter start and end.
 */
class FirstBetween
{
    /**
     * @param string $text
     * @param string $start
     * @param string $end
     *
     * @return string
     */
    public function __invoke(
        string $text,
        string $start,
        string $end
    ): string {
        $result = mb_strstr($text, $start);
        $result = (false === $result) ? '' : $result;
        $result = mb_strstr($result, $end, true);
        $result = (false === $result) ? '' : $result;

        return trim($result, $start . $end);
    }
}
