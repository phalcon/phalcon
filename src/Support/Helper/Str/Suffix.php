<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Support\Helper\Str;

use Stringable;

/**
 * Suffixes the text with the supplied suffix
 */
class Suffix
{
    /**
     * @param scalar|Stringable|null $text
     * @param string                 $suffix
     *
     * @return string
     */
    public function __invoke(mixed $text, string $suffix): string
    {
        return ((string)$text) . $suffix;
    }
}
