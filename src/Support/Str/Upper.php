<?php

/**
 * This file is part of the Phalcon.
 *
 * (c) Phalcon Team <team@phalcon.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Support\Str;

use Phalcon\Support\Str\Traits\UpperTrait;

/**
 * Class Upper
 *
 * @package Phalcon\Support\Str
 */
class Upper
{
    use UpperTrait;

    /**
     * Uppercases a string using mbstring
     *
     * @param string $text
     * @param string $encoding
     *
     * @return string
     */
    public function __invoke(
        string $text,
        string $encoding = 'UTF-8'
    ): string {
        return $this->toUpper($text, $encoding);
    }
}
