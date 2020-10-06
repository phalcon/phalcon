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

namespace Phalcon\Support\Str;

use function mb_convert_case;

use const MB_CASE_TITLE;

/**
 * Class Prefix
 *
 * @package Phalcon\Support\Str
 */
class Prefix
{
    /**
     * Prefixes the text with the supplied prefix
     *
     * @param mixed  $text
     * @param string $prefix
     *
     * @return string
     */
    public function __invoke($text, string $prefix): string
    {
        return $prefix . ((string) $text);
    }
}
