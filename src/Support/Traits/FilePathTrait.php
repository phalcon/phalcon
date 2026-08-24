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
use function strtolower;
use function substr;

trait FilePathTrait
{
    public function prepareVirtualPath(string $key, string $separator = '_'): string
    {
        /**
         * Match Zephir's `prepare_virtual_path` kernel function: stop at the
         * first null byte, replace the path separators, and lower-case the
         * rest of the key.
         */
        $nul = strpos($key, "\0");
        if (false !== $nul) {
            $key = substr($key, 0, $nul);
        }

        return str_replace(['/', '\\', ':'], $separator, strtolower($key));
    }
}
