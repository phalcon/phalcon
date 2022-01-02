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

use function lcfirst;

/**
 * Converts strings to upperCamelCase or lowerCamelCase
 */
class Camelize extends PascalCase
{
    /**
     * @param string      $text
     * @param string|null $delimiters
     * @param bool        $lowerFirst
     *
     * @return string
     */
    public function __invoke(
        string $text,
        string $delimiters = null,
        bool $lowerFirst = false
    ): string {
        $result = parent::__invoke($text, $delimiters);

        if (true === $lowerFirst) {
            $result = lcfirst($result);
        }

        return $result;
    }
}
