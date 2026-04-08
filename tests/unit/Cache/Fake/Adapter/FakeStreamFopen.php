<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Cache\Fake\Adapter;

use Phalcon\Cache\Adapter\Stream;

class FakeStreamFopen extends Stream
{
    protected function phpFopen(string $filename, string $mode): mixed
    {
        return false;
    }
}
