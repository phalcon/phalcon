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

namespace Phalcon\Tests\Unit\Http\Message\UploadedFile;

use Phalcon\Http\Message\Exception\RuntimeException;
use Phalcon\Http\Message\Interfaces\StreamInterface;
use Phalcon\Http\Message\Stream;
use Phalcon\Http\Message\UploadedFile;
use Phalcon\Tests\Fixtures\Page\Http;
use Phalcon\Tests\AbstractUnitTestCase;

use function outputDir;

use const UPLOAD_ERR_CANT_WRITE;

final class GetStreamTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Http\Message\UploadedFile :: getStream()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function testHttpMessageUploadedFileGetStream(): void
    {
        $stream = new Stream(Http::STREAM_MEMORY);
        $file   = new UploadedFile(
            $stream,
            0,
            UPLOAD_ERR_OK,
            'phalcon.txt'
        );

        $expected = $stream;
        $actual   = $file->getStream();
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Message\UploadedFile :: getStream() - exception
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function testHttpMessageUploadedFileGetStreamException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to write file to disk.');

        $stream = new Stream(Http::STREAM_MEMORY);
        $file   = new UploadedFile(
            $stream,
            0,
            UPLOAD_ERR_CANT_WRITE,
            'phalcon.txt'
        );

        $file->getStream();
    }

    /**
     * Tests Phalcon\Http\Message\UploadedFile :: getStream() - exception
     * already moved
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function testHttpMessageUploadedFileGetStreamExceptionAlreadyMoved(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'The file has already been moved to the target location'
        );

        $stream = new Stream(Http::STREAM_MEMORY, 'w+b');
        $stream->write('Phalcon Framework');

        $file   = new UploadedFile($stream, 0);
        $target = $this->getNewFileName();
        $target = outputDir(
            'tests/stream/' . $target
        );

        $file->moveTo($target);
        $file->getStream();
    }

    /**
     * Tests Phalcon\Http\Message\UploadedFile :: getStream() - string
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function testHttpMessageUploadedFileGetStreamString(): void
    {
        $file = new UploadedFile(
            Http::STREAM_MEMORY,
            0,
            UPLOAD_ERR_OK,
            'phalcon.txt'
        );

        $actual = $file->getStream();
        $this->assertInstanceOf(StreamInterface::class, $actual);
    }
}
