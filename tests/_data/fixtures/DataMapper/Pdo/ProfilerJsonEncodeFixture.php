<?php

/**
* This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Fixtures\DataMapper\Pdo;

use Phalcon\DataMapper\Pdo\Profiler\Profiler;

class ProfilerJsonEncodeFixture extends Profiler
{
    /**
     * @param mixed $value
     * @param int   $flags
     * @param int   $depth
     *
     * @return false|string
     *
     * @link https://php.net/manual/en/function.json-encode.php
     */
    protected function phpJsonEncode(
        mixed $value,
        int $flags = 0,
        int $depth = 512
    ): false | string {
        return false;
    }
}
