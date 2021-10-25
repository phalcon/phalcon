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

use function mb_strtolower;

/**
 * Converts strings to non camelized style
 */
class Uncamelize
{
    /**
     * @param string $text
     * @param string $delimiters
     *
     * @return string
     */
    public function __invoke(
        string $text,
        string $delimiter = '_'
    ): string {
        return mb_strtolower(
            preg_replace(
                '/[A-Z]/',
                $delimiter . '\\0',
                lcfirst($text)
            )
        );
    }
}
