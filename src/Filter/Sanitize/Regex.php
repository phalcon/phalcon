<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Filter\Sanitize;

use Phalcon\Contracts\Filter\Sanitizer;

use function preg_replace;

/**
 * Sanitizes a value performing preg_replace
 */
class Regex implements Sanitizer
{
    /**
     * @param string[]|string $input
     * @param string[]|string $pattern
     * @param string[]|string $replace
     *
     * @return string|string[]|null
     */
    public function __invoke(
        array | string $input,
        array | string $pattern,
        array | string $replace
    ) {
        return preg_replace($pattern, $replace, $input);
    }
}
