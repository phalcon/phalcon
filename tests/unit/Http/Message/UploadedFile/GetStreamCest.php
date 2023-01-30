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

use Page\Http;
use Phalcon\Http\Message\Exception\RuntimeException;
use Phalcon\Http\Message\Interfaces\StreamInterface;
use Phalcon\Http\Message\Stream;
use Phalcon\Http\Message\UploadedFile;
use UnitTester;

use function outputDir;

use const UPLOAD_ERR_CANT_WRITE;

class GetStreamCest
{
    /**
     * Tests Phalcon\Http\Message\UploadedFile :: getStream()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function httpMessageUploadedFileGetStream(UnitTester $I)
    {
        $I->wantToTest('Http\Message\UploadedFile - getStream()');
        $stream = new Stream(Http::STREAM_MEMORY);
        $file   = new UploadedFile(
            $stream,
            0,
            UPLOAD_ERR_OK,
            'phalcon.txt'
        );

        $expected = $stream;
        $actual   = $file->getStream();
        $I->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Message\UploadedFile :: getStream() - string
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function httpMessageUploadedFileGetStreamString(UnitTester $I)
    {
        $I->wantToTest('Http\Message\UploadedFile - getStream() - string');
        $file = new UploadedFile(
            Http::STREAM_MEMORY,
            0,
            UPLOAD_ERR_OK,
            'phalcon.txt'
        );

        $actual = $file->getStream();
        $I->assertInstanceOf(StreamInterface::class, $actual);
    }

    /**
     * Tests Phalcon\Http\Message\UploadedFile :: getStream() - exception
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function httpMessageUploadedFileGetStreamException(UnitTester $I)
    {
        $I->wantToTest('Http\Message\UploadedFile - getStream() - exception');

        $I->expectThrowable(
            new RuntimeException('Failed to write file to disk.'),
            function () {
                $stream = new Stream(Http::STREAM_MEMORY);
                $file   = new UploadedFile(
                    $stream,
                    0,
                    UPLOAD_ERR_CANT_WRITE,
                    'phalcon.txt'
                );

                $actual = $file->getStream();
            }
        );
    }

    /**
     * Tests Phalcon\Http\Message\UploadedFile :: getStream() - exception
     * already moved
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function httpMessageUploadedFileGetStreamExceptionAlreadyMoved(
        UnitTester $I
    ) {
        $I->wantToTest(
            'Http\Message\UploadedFile - getStream() - exception already moved'
        );

        $I->expectThrowable(
            new RuntimeException(
                'The file has already been moved to the target location'
            ),
            function () use ($I) {
                $stream = new Stream(Http::STREAM_MEMORY, 'w+b');
                $stream->write('Phalcon Framework');

                $file = new UploadedFile($stream, 0);
                $target = $I->getNewFileName();
                $target = outputDir(
                    'tests/stream/' . $target
                );

                $file->moveTo($target);
                $actual = $file->getStream();
            }
        );
    }
}
