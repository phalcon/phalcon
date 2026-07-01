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
use Phalcon\Tests\AbstractUnitTestCase;

final class OperationsTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Http\Message\UploadedFile :: __construct() - invalid stream
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageUploadedFileConstructInvalidStreamThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid stream or file passed');
        new UploadedFile(12345, null, 0);
    }

    /**
     * Tests Phalcon\Http\Message\UploadedFile :: __construct() - with filename
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageUploadedFileConstructWithFilename(): void
    {
        $uploaded = new UploadedFile('php://memory', null, 0);

        $this->assertNull($uploaded->getSize());
        $this->assertNotNull($uploaded->getStream());
    }

    /**
     * Tests Phalcon\Http\Message\UploadedFile :: __construct() - with resource
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageUploadedFileConstructWithResource(): void
    {
        $resource = fopen('php://memory', 'r+b');
        $uploaded = new UploadedFile($resource, null, 0);

        $this->assertNull($uploaded->getSize());
        $this->assertNotNull($uploaded->getStream());
    }
    /**
     * Tests Phalcon\Http\Message\UploadedFile :: __construct() - with stream
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageUploadedFileConstructWithStream(): void
    {
        $stream   = new Stream('php://memory', 'r+b');
        $uploaded = new UploadedFile($stream, 100, 0, 'file.txt', 'text/plain');

        $this->assertSame(100, $uploaded->getSize());
        $this->assertSame(0, $uploaded->getError());
        $this->assertSame('file.txt', $uploaded->getClientFilename());
        $this->assertSame('text/plain', $uploaded->getClientMediaType());
        $this->assertSame($stream, $uploaded->getStream());
    }

    /**
     * Tests Phalcon\Http\Message\UploadedFile :: getStream() - already moved
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageUploadedFileGetStreamAlreadyMovedThrows(): void
    {
        $stream2  = new Stream('php://memory', 'r+b');
        $stream2->write('content');
        $tmpFile   = sys_get_temp_dir() . '/phalcon_test_' . uniqid() . '.txt';
        $uploaded2 = new UploadedFile($stream2, null, 0);
        $uploaded2->moveTo($tmpFile);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The file has already been moved');
        $uploaded2->getStream();

        if (file_exists($tmpFile)) {
            unlink($tmpFile);
        }
    }

    /**
     * Tests Phalcon\Http\Message\UploadedFile :: getStream() - error throws
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageUploadedFileGetStreamErrorThrows(): void
    {
        $stream   = new Stream('php://memory', 'r+b');
        $uploaded = new UploadedFile($stream, null, UPLOAD_ERR_NO_FILE);

        $this->expectException(RuntimeException::class);
        $uploaded->getStream();
    }

    /**
     * Tests Phalcon\Http\Message\UploadedFile :: __construct() - invalid error
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageUploadedFileInvalidErrorThrows(): void
    {
        $stream = new Stream('php://memory', 'r+b');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid error');
        new UploadedFile($stream, null, 9);
    }

    /**
     * Tests Phalcon\Http\Message\UploadedFile :: moveTo() - already moved throws
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageUploadedFileMoveToAlreadyMovedThrows(): void
    {
        $stream   = new Stream('php://memory', 'r+b');
        $uploaded = new UploadedFile($stream, null, 0);

        // Force alreadyMoved = true by moving once to a valid path
        // We simulate this by trying to move to a non-existent dir to get the
        // InvalidArgumentException, then test the alreadyMoved path via
        // getStream after a move
        try {
            $uploaded->moveTo('/nonexistent/path/file.txt');
        } catch (InvalidArgumentException) {
            // expected - target dir doesn't exist
        }

        // Now test: after marking alreadyMoved, getStream should throw
        // We test this by reflecting on alreadyMoved behavior indirectly
        // Testing moveTo with already-moved: need to get it into that state first
        // Use a valid write-once approach by using a temp file
        $tmpFile = sys_get_temp_dir() . '/phalcon_test_' . uniqid() . '.txt';
        $stream2  = new Stream('php://memory', 'r+b');
        $stream2->write('content');
        $uploaded2 = new UploadedFile($stream2, null, 0);
        $uploaded2->moveTo($tmpFile);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('File has already been moved');
        $uploaded2->moveTo($tmpFile . '_2');

        if (file_exists($tmpFile)) {
            unlink($tmpFile);
        }
    }

    /**
     * Tests Phalcon\Http\Message\UploadedFile :: moveTo() - invalid target throws
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageUploadedFileMoveToInvalidTargetThrows(): void
    {
        $stream   = new Stream('php://memory', 'r+b');
        $uploaded = new UploadedFile($stream, null, 0);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Target folder is empty string');
        $uploaded->moveTo('');
    }

    /**
     * Tests Phalcon\Http\Message\UploadedFile :: moveTo() - error throws
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageUploadedFileMoveToWithErrorThrows(): void
    {
        $stream   = new Stream('php://memory', 'r+b');
        $uploaded = new UploadedFile($stream, null, UPLOAD_ERR_NO_FILE);

        $this->expectException(RuntimeException::class);
        $uploaded->moveTo('/some/target/path.txt');
    }
}
