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

/**
 * Suffixes the text with the supplied suffix
 */
class Suffix
{
    /**
     * @param mixed  $text
     * @param string $suffix
     *
     * @return string
     */
    public function __invoke($text, string $suffix): string
    {
        return ((string) $text) . $suffix;
    }
}
