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

use function implode;
use function mb_str_split;
use function mb_substr;
use function pathinfo;

use const PATHINFO_FILENAME;

/**
 * Class DirFromFile
 *
 * @package Phalcon\Support\Str
 */
class DirFromFile
{
    /**
     * Accepts a file name (without extension) and returns a calculated
     * directory structure with the filename in the end
     *
     * @param string $file
     *
     * @return string
     */
    public function __invoke(string $file): string
    {
        $name  = pathinfo($file, PATHINFO_FILENAME);
        $start = mb_substr($name, 0, -2);

        if (!$start) {
            $start = mb_substr($name, 0, 1);
        }

        return implode('/', mb_str_split($start, 2)) . '/';
    }
}
