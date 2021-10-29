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
 * Converts strings to non camelized style
 */
class Uncamelize
{
    use LowerTrait;

    /**
     * @param string $text
     * @param string $delimiters
     *
     * @return string
     */
    public function __invoke(
        string $text,
        string $delimiter = '_'
    ): string {
        $text = (string) preg_replace(
            '/[A-Z]/',
            $delimiter . '\\0',
            lcfirst($text)
        );

        return $this->toLower($text);
    }
}
