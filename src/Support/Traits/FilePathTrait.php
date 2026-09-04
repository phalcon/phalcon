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

namespace Phalcon\Support\Traits;

use function str_replace;
use function strpos;
use function is_string;
use function strtolower;
use function substr;

trait FilePathTrait
{
    public function prepareVirtualPath(mixed $key, string $separator = '_'): string
    {
        /**
         * Match Zephir's `prepare_virtual_path` kernel function: a key that is
         * not a string gives back an empty path, then stop at the first null
         * byte, replace the path separators, and lower-case the rest.
         */
        if (!is_string($key)) {
            return '';
        }

        $nul = strpos($key, "\0");
        if (false !== $nul) {
            $key = substr($key, 0, $nul);
        }

        return str_replace(['/', '\\', ':'], $separator, strtolower($key));
    }
}
