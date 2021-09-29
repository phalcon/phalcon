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

use function rtrim;

use const DIRECTORY_SEPARATOR;

/**
 * Class DirSeparator
 *
 * @package Phalcon\Support\Str
 */
class DirSeparator
{
    /**
     * Accepts a directory name and ensures that it ends with
     * DIRECTORY_SEPARATOR
     *
     * @param string $directory
     *
     * @return string
     */
    public function __invoke(string $directory): string
    {
        return rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }
}
