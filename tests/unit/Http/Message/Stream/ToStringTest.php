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

namespace Phalcon\Tests\Unit\Http\Message\Stream;

use Phalcon\Http\Message\Stream;
use Phalcon\Tests\AbstractUnitTestCase;

final class ToStringTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Http\Message\Stream :: __toString()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function testHttpMessageStreamToString(): void
    {
        $fileName = dataDir('assets/stream/mit.txt');
        $expected = file_get_contents($fileName);
        $stream   = new Stream($fileName, 'rb');

        $this->assertSame(
            $expected,
            (string)$stream
        );

        $this->assertSame(
            $expected,
            $stream->__toString()
        );
    }
}
