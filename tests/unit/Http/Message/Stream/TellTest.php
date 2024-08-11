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
use RuntimeException;

final class TellTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Http\Message\Stream :: tell()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function testHttpMessageStreamTell(): void
    {
        $fileName = dataDir('assets/stream/mit.txt');
        $handle   = fopen($fileName, 'rb');
        $stream   = new Stream($handle);

        $expected = 274;
        fseek($handle, $expected);
        $actual = $stream->tell();
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Message\Stream :: tell() - detached
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function testHttpMessageStreamTellDetached(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('A valid resource is required.');

        $fileName = dataDir('assets/stream/mit.txt');
        $stream   = new Stream($fileName, 'rb');
        $stream->detach();

        $stream->tell();
    }
}
