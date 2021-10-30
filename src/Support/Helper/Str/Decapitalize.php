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
use Phalcon\Traits\Helper\Str\UpperTrait;

use function mb_substr;

/**
 * Decapitalizes the first letter of the string and then adds it with rest
 * of the string. Omit the upperRest parameter to keep the rest of the
 * string intact, or set it to true to convert to uppercase.
 */
class Decapitalize
{
    use LowerTrait;
    use UpperTrait;

    /**
     * @param string $text
     * @param bool   $upperRest
     * @param string $encoding
     *
     * @return string
     */
    public function __invoke(
        string $text,
        bool $upperRest = false,
        string $encoding = 'UTF-8'
    ): string {
        $substr = mb_substr($text, 1);
        $suffix = ($upperRest) ? $this->toUpper($substr, $encoding) : $substr;

        return $this->toLower(mb_substr($text, 0, 1), $encoding) . $suffix;
    }
}
