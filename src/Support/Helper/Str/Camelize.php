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

use Phalcon\Traits\Helper\Str\CamelizeTrait;

/**
 * Converts strings to upperCamelCase or lowerCamelCase
 */
class Camelize
{
    use CamelizeTrait;

    /**
     * @param string      $text
     * @param string|null $delimiters
     * @param bool        $lowerFirst
     *
     * @return string
     */
    public function __invoke(
        string $text,
        string | null $delimiters = null,
        bool $lowerFirst = false
    ): string {
        /**
         * @todo Make the delimiters specific as in the trait
         */
        $delimiters = $delimiters ?: '\-_';

        return $this->toCamelize($text, $delimiters, $lowerFirst);
    }
}
