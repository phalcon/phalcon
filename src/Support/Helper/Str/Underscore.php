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

use function preg_replace;
use function trim;

/**
 * Makes a text underscored instead of spaced
 */
class Underscore
{
    /**
     * @param string $text
     *
     * @return string
     */
    public function __invoke(string $text): string
    {
        $result = preg_replace('#\s+#', '_', trim($text));

        return (null === $result) ? '' : $result;
    }
}
