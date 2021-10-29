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

use Phalcon\Traits\Helper\Str\LowerTrait;

/**
 * Converts a string to lowercase using mbstring
 */
class Lower
{
    use LowerTrait;

    /**
     * @param string $text
     * @param string $encoding
     *
     * @return string
     */
    public function __invoke(
        string $text,
        string $encoding = 'UTF-8'
    ): string {
        return $this->toLower($text, $encoding);
    }
}
