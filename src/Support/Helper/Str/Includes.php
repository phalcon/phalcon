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

use function mb_strpos;

/**
 * Determines whether a string includes another string or not.
 */
class Includes
{
    /**
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
