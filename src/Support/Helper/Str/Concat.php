<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Support\Helper\Str;

use Phalcon\Support\Helper\Str\Exceptions\InsufficientArguments;

use function end;
use function implode;
use function trim;

/**
 * Concatenates strings using the separator only once without duplication in
 * places concatenation
 */
class Concat extends AbstractStr
{
    /**
     * @throws InsufficientArguments
     */
    public function __invoke(string $delimiter, string ...$many): string
    {
        $data   = [];
        $prefix = "";
        $suffix = "";

        if (count($many) < 2) {
            throw new InsufficientArguments();
        }

        $first = reset($many);
        $last  = end($many);

        if ($this->toStartsWith($first, $delimiter, false)) {
            $prefix = $delimiter;
        }

        if ($this->toEndsWith($last, $delimiter, false)) {
            $suffix = $delimiter;
        }

        foreach ($many as $item) {
            $data[] = trim($item, $delimiter);
        }

        return $prefix . implode($delimiter, $data) . $suffix;
    }
}
