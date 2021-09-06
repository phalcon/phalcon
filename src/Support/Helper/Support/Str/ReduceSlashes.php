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

use function preg_replace;

/**
 * Class ReduceSlashes
 *
 * @package Phalcon\Support\Str
 */
class ReduceSlashes
{
    /**
     * Reduces multiple slashes in a string to single slashes
     *
     * @param string $text
     *
     * @return string
     */
    public function __invoke(string $text): string
    {
        $result = preg_replace('#(?<!:)//+#', '/', $text);

        return (null === $result) ? '' : $result;
    }
}
