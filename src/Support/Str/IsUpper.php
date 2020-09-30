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

use Phalcon\Support\Str\Traits\UpperTrait;

class IsUpper
{
    use UpperTrait;

    /**
     * Returns true if the given string is upper case, false otherwise.
     *
     * @param string $text
     * @param string $encoding
     *
     * @return bool
     */
    public function __invoke(
        string $text,
        string $encoding = 'UTF-8'
    ): bool {
        return $text === $this->toUpper($text, $encoding);
    }
}
