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

use Phalcon\Support\Str\Traits\InterpolateTrait;

/**
 * Class Interpolate
 *
 * @package Phalcon\Support\Str
 */
class Interpolate
{
    use InterpolateTrait;

    /**
     * Interpolates context values into the message placeholders
     *
     * @see http://www.php-fig.org/psr/psr-3/ Section 1.2 Message
     *
     * @param string $input
     * @param array  $context
     *
     * @return string
     */
    public function __invoke(string $input, array $context = []): string
    {
        return $this->toInterpolate($input, $context);
    }
}
