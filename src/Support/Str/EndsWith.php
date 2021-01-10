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

namespace Phiz\Support\Str;

use Phiz\Support\Str\Traits\EndsWithTrait;

/**
 * Class EndsWith
 *
 * @package Phiz\Support\Str
 */
class EndsWith
{
    use EndsWithTrait;

    /**
     * Check if a string ends with a given string
     *
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
        return $this->toEndsWith($haystack, $needle, $ignoreCase);
    }
}
