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
use Phalcon\Tests\Fixtures\Http\Message\StreamFixture;
use Phalcon\Tests\Fixtures\Page\Http;
use Phalcon\Tests\AbstractUnitTestCase;

final class GetMetadataTest extends AbstractUnitTestCase
{
    public static function getExamples(): array
    {
        return [
            ['timed_out', false,],
            ['blocked', true,],
            ['eof', false,],
            ['wrapper_type', 'plainfile',],
            ['stream_type', 'STDIO',],
            ['mode', 'rb',],
            ['unread_bytes', 0,],
            ['seekable', true,],
            ['uri', dataDir('assets/stream/mit.txt'),],
            ['unknown', [],],
        ];
    }

    /**
     * Tests Phalcon\Http\Message\Stream :: getMetadata()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function testHttpMessageStreamGetMetadata(): void
    {
        $fileName = dataDir('assets/stream/mit.txt');
        $handle   = fopen($fileName, 'rb');
        $stream   = new Stream($handle);

        $expected = [
            'timed_out'    => false,
            'blocked'      => true,
            'eof'          => false,
            'wrapper_type' => 'plainfile',
            'stream_type'  => 'STDIO',
            'mode'         => 'rb',
            'unread_bytes' => 0,
            'seekable'     => true,
            'uri'          => $fileName,
        ];

        $actual = $stream->getMetadata();
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Message\Stream :: getMetadata() - by key
     *
     * @dataProvider getExamples
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2019-02-10
     */
    public function testHttpMessageStreamGetMetadataByKey(
        string $key,
        mixed $expected
    ) {
        $fileName = dataDir('assets/stream/mit.txt');
        $handle   = fopen($fileName, 'rb');
        $stream   = new Stream($handle);

        $actual = $stream->getMetadata($key);
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Message\Stream :: getMetadata() - invalid handle
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2019-02-10
     */
    public function testHttpMessageStreamGetMetadataInvalidHandle(): void
    {
        $stream = new StreamFixture(Http::STREAM_MEMORY, 'rb');
        $stream->setHandle(null);

        $actual = $stream->getMetadata();
        $this->assertNull($actual);
    }
}
