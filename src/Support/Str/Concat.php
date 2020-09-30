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

use Phalcon\Support\Str\Traits\EndsWithTrait;
use Phalcon\Support\Str\Traits\StartsWithTrait;

use function array_merge;
use function end;
use function implode;
use function trim;

/**
 * Class Concat
 *
 * @package Phalcon\Support\Str
 */
class Concat
{
    use EndsWithTrait;
    use StartsWithTrait;

    /**
     * Concatenates strings using the separator only once without duplication in
     * places concatenation
     *
     * ```php
     * $str = Phalcon\Helper\Str::concat(
     *     '/',
     *     '/tmp/',
     *     '/folder_1/',
     *     '/folder_2',
     *     'folder_3/'
     * );
     *
     * echo $str;   // /tmp/folder_1/folder_2/folder_3/
     * ```
     *
     * @param string $delimiter
     * @param string $first
     * @param string $second
     * @param string ...$arguments
     *
     * @return string
     */
    public function __invoke(
        string $delimiter,
        string $first,
        string $second,
        string ...$arguments
    ): string {
        $data       = [];
        $parameters = array_merge([$first, $second], $arguments);
        $last       = end($parameters) ?? $second;

        $prefix = $this->toStartsWith($first, $delimiter) ? $delimiter : '';
        $suffix = $this->toEndsWith($last, $delimiter) ? $delimiter : '';

        foreach ($parameters as $parameter) {
            $data[] = trim($parameter, $delimiter);
        }

        return $prefix . implode($delimiter, $data) . $suffix;
    }
}
