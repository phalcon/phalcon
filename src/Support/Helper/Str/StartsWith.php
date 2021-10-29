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

use Phalcon\Traits\Helper\Str\StartsWithTrait;

/**
 * Check if a string starts with a given string
 */
class StartsWith
{
    use StartsWithTrait;

    /**
     * @param string $haystack
     * @param string $needle
     * @param bool   $ignoreCase
     *
     * @return bool
     */
    public function __invoke(
        string $haystack,
        string $needle,
        bool $ignoreCase = true
    ): bool {
        return $this->toStartsWith($haystack, $needle, $ignoreCase);
    }
}
