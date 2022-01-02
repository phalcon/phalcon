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

use function array_map;
use function implode;
use function lcfirst;
use function mb_strtolower;
use function preg_replace;
use function preg_split;
use function str_replace;
use function ucfirst;

use function ucwords;

use const PREG_SPLIT_DELIM_CAPTURE;
use const PREG_SPLIT_NO_EMPTY;

/**
 * Converts strings to snake_case style
 */
class SnakeCase extends PascalCase
{
    /**
     * @param string      $text
     * @param string|null $delimiters
     *
     * @return string
     */
    public function __invoke(
        string $text,
        string $delimiters = null
    ): string
    {
        $output = $this->processArray($text, $delimiters);

        return implode('-', $output);
    }
}
