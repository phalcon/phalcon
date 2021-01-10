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

namespace Phiz\Support\Str\Traits;

/**
 * Trait InterpolateTrait
 *
 * @package Phiz\Support\Str
 */
trait InterpolateTrait
{
    /**
     * Interpolates context values into the message placeholders
     *
     * @see http://www.php-fig.org/psr/psr-3/ Section 1.2 Message
     *
     * @param string $input
     * @param array  $context
     * @param string $left
     * @param string $right
     *
     * @return string
     */
    private function toInterpolate(
        string $input,
        array $context = [],
        string $left = '{',
        string $right = '}'
    ): string {
        if (empty($context)) {
            return $input;
        }

        $replace = [];
        foreach ($context as $key => $value) {
            $replace[$left . $key . $right] = $value;
        }

        return strtr($input, $replace);
    }
}
