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

use Phalcon\Traits\Helper\Str\UncamelizeTrait;

/**
 * Converts strings to non camelized style
 */
class Uncamelize
{
    use UncamelizeTrait;

    /**
     * @param string $text
     * @param string $delimiter
     *
     * @return string
     */
    public function __invoke(
        string $text,
        string $delimiter = '_'
    ): string {
        return $this->toUncamelize($text, $delimiter);
    }
}
