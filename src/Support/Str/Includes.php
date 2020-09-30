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

use function mb_strpos;

/**
 * Class Includes
 *
 * @package Phalcon\Support\Str
 */
class Includes
{
    /**
     * Lets you determine whether or not a string includes another string.
     *
     * @param string $haystack
     * @param string $needle
     *
     * @return bool
     */
    public function __invoke(
        string $haystack,
        string $needle
    ): bool {
        return false !== mb_strpos($haystack, $needle);
    }
}
