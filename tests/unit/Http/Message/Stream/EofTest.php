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

final class EofTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Http\Message\Stream :: eof()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function testHttpMessageStreamEof(): void
    {
        $fileName = dataDir('assets/stream/mit.txt');
        $handle   = fopen($fileName, 'rb');
        $stream   = new Stream($handle);
        while (true !== feof($handle)) {
            fread($handle, 1024);
        }

        $actual = $stream->eof();
        $this->assertTrue($actual);
    }

    /**
     * Tests Phalcon\Http\Message\Stream :: eof() - detached stream
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function testHttpMessageStreamEofDetached(): void
    {
        $fileName = dataDir('assets/stream/mit.txt');
        $stream   = new Stream($fileName, 'rb');
        $stream->detach();

        $actual = $stream->eof();
        $this->assertTrue($actual);
    }

    /**
     * Tests Phalcon\Http\Message\Stream :: eof() - not at eof
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function testHttpMessageStreamEofNotAtEof(): void
    {
        $fileName = dataDir('assets/stream/mit.txt');
        $stream   = new Stream($fileName, 'rb');
        $stream->seek(10);

        $actual = $stream->eof();
        $this->assertFalse($actual);
    }
}
