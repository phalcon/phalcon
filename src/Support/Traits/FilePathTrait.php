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

trait FilePathTrait
{
    public function prepareVirtualPath(string $key, string $separator = '_'): string
    {
        return str_replace(['/', '\\', ':'], $separator, $key);
    }
}
