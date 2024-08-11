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

use Phalcon\Http\Message\Exception\InvalidArgumentException;
use Phalcon\Http\Message\Exception\RuntimeException;
use Phalcon\Http\Message\Stream;
use Phalcon\Http\Message\UploadedFile;
use Phalcon\Tests\Fixtures\Page\Http;
use Phalcon\Tests\AbstractUnitTestCase;

use function outputDir;

final class MoveToTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Http\Message\UploadedFile :: moveTo()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function testHttpMessageUploadedFileMoveTo(): void
    {
        $stream = new Stream(Http::STREAM_MEMORY, 'w+b');

        $stream->write('Phalcon Framework');

        $file   = new UploadedFile($stream, 0);
        $target = $this->getNewFileName();
        $target = outputDir('tests/stream/' . $target);

        $file->moveTo($target);
        $this->assertFileExists($target);

        $this->assertFileContentsEqual($target, (string)$stream);
    }

    /**
     * Tests Phalcon\Http\Message\UploadedFile :: moveTo() - already moved
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function testHttpMessageUploadedFileMoveToAlreadyMoved(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('File has already been moved');

        $stream = new Stream(Http::STREAM_MEMORY, 'w+b');
        $stream->write('Phalcon Framework');

        $file   = new UploadedFile($stream, 0);
        $target = $this->getNewFileName();

        $target = outputDir('tests/stream/' . $target);

        $file->moveTo($target);
        $file->moveTo($target);
    }

    /**
     * Tests Phalcon\Http\Message\UploadedFile :: moveTo() - upload error
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function testHttpMessageUploadedFileMoveToUploadError(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to write file to disk.');

        $stream = new Stream(Http::STREAM_MEMORY, 'w+b');

        $stream->write('Phalcon Framework');

        $target = $this->getNewFileName();
        $target = outputDir('tests/stream/' . $target);
        $file   = new UploadedFile($stream, 0, UPLOAD_ERR_CANT_WRITE);

        $file->moveTo($target);
    }

    /**
     * Tests Phalcon\Http\Message\UploadedFile :: moveTo() - wrong path
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function testHttpMessageUploadedFileMoveToWrongPath(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Target folder is empty string, not a folder or not writable'
        );

        $stream = new Stream(Http::STREAM_MEMORY, 'w+b');
        $stream->write('Phalcon Framework');

        $file = new UploadedFile($stream, 0);
        $file->moveTo("");
    }
}
