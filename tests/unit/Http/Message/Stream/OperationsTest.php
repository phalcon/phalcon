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

use Phalcon\Http\Message\Exception\InvalidArgumentException;
use Phalcon\Http\Message\Exception\RuntimeException;
use Phalcon\Http\Message\Stream;
use Phalcon\Http\Message\Stream\Memory;
use Phalcon\Http\Message\Stream\Temp;
use Phalcon\Tests\AbstractUnitTestCase;

final class OperationsTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Http\Message\Stream :: __construct() and basic operations
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageStreamBasicOperations(): void
    {
        $stream = new Stream('php://memory', 'r+b');

        $this->assertTrue($stream->isReadable());
        $this->assertTrue($stream->isWritable());
        $this->assertTrue($stream->isSeekable());
        $this->assertFalse($stream->eof());
    }

    /**
     * Tests Phalcon\Http\Message\Stream :: write() and read()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageStreamWriteRead(): void
    {
        $stream = new Stream('php://memory', 'r+b');
        $stream->write('hello world');
        $stream->rewind();

        $actual = $stream->read(5);
        $this->assertSame('hello', $actual);

        $actual = $stream->getContents();
        $this->assertSame(' world', $actual);
    }

    /**
     * Tests Phalcon\Http\Message\Stream :: __toString()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageStreamToString(): void
    {
        $stream = new Stream('php://memory', 'r+b');
        $stream->write('test content');

        $this->assertSame('test content', (string) $stream);
    }

    /**
     * Tests Phalcon\Http\Message\Stream :: getSize()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageStreamGetSize(): void
    {
        $stream = new Stream('php://memory', 'r+b');
        $stream->write('12345');

        $this->assertSame(5, $stream->getSize());
    }

    /**
     * Tests Phalcon\Http\Message\Stream :: tell()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageStreamTell(): void
    {
        $stream = new Stream('php://memory', 'r+b');
        $stream->write('hello');

        $this->assertSame(5, $stream->tell());
    }

    /**
     * Tests Phalcon\Http\Message\Stream :: seek()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageStreamSeek(): void
    {
        $stream = new Stream('php://memory', 'r+b');
        $stream->write('hello world');
        $stream->seek(6);

        $this->assertSame(6, $stream->tell());
        $this->assertSame('world', $stream->read(5));
    }

    /**
     * Tests Phalcon\Http\Message\Stream :: getMetadata()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageStreamGetMetadata(): void
    {
        $stream = new Stream('php://memory', 'r+b');

        $metadata = $stream->getMetadata();
        $this->assertIsArray($metadata);

        $mode = $stream->getMetadata('mode');
        $this->assertIsString($mode);

        $null = $stream->getMetadata('nonexistent');
        $this->assertNull($null);
    }

    /**
     * Tests Phalcon\Http\Message\Stream :: detach() and operations after detach
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageStreamDetach(): void
    {
        $stream   = new Stream('php://memory', 'r+b');
        $resource = $stream->detach();

        $this->assertIsResource($resource);
        $this->assertNull($stream->detach());
        $this->assertNull($stream->getSize());
        $this->assertFalse($stream->isReadable());
        $this->assertFalse($stream->isWritable());
        $this->assertFalse($stream->isSeekable());
    }

    /**
     * Tests Phalcon\Http\Message\Stream :: eof() after reading all content
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageStreamEof(): void
    {
        $stream = new Stream('php://memory', 'r+b');
        $stream->write('hi');
        $stream->rewind();
        $stream->read(100);

        $this->assertTrue($stream->eof());
    }

    /**
     * Tests Phalcon\Http\Message\Stream :: tell() throws after detach
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageStreamTellThrowsWhenDetached(): void
    {
        $stream = new Stream('php://memory', 'r+b');
        $stream->detach();

        $this->expectException(RuntimeException::class);
        $stream->tell();
    }

    /**
     * Tests Phalcon\Http\Message\Stream :: seek() throws after detach
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageStreamSeekThrowsWhenDetached(): void
    {
        $stream = new Stream('php://memory', 'r+b');
        $stream->detach();

        $this->expectException(RuntimeException::class);
        $stream->seek(0);
    }

    /**
     * Tests Phalcon\Http\Message\Stream :: read() throws after detach
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageStreamReadThrowsWhenNotReadable(): void
    {
        $stream = new Stream('php://memory', 'r+b');
        $stream->detach();

        $this->expectException(RuntimeException::class);
        $stream->read(10);
    }

    /**
     * Tests Phalcon\Http\Message\Stream :: write() throws after detach
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageStreamWriteThrowsWhenNotWritable(): void
    {
        $stream = new Stream('php://memory', 'r+b');
        $stream->detach();

        $this->expectException(RuntimeException::class);
        $stream->write('data');
    }

    /**
     * Tests Phalcon\Http\Message\Stream :: getContents() throws after detach
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageStreamGetContentsThrowsWhenNotReadable(): void
    {
        $stream = new Stream('php://memory', 'r+b');
        $stream->detach();

        $this->expectException(RuntimeException::class);
        $stream->getContents();
    }

    /**
     * Tests Phalcon\Http\Message\Stream :: eof() - after detach returns true
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageStreamEofAfterDetachReturnsTrue(): void
    {
        $stream = new Stream('php://memory', 'r+b');
        $stream->detach();

        $this->assertTrue($stream->eof());
    }

    /**
     * Tests Phalcon\Http\Message\Stream :: setStream() - invalid stream throws
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageStreamSetStreamInvalidThrows(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The stream provided is not valid');
        new Stream(12345);
    }

    /**
     * Tests Phalcon\Http\Message\Stream :: write() - not writable throws
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageStreamWriteNotWritableThrows(): void
    {
        $stream = new Stream('php://memory', 'r');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The resource is not writable.');
        $stream->write('data');
    }

    /**
     * Tests Phalcon\Http\Message\Stream\Memory :: __construct()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageStreamMemoryConstruct(): void
    {
        $stream = new Memory('r+b');

        $this->assertInstanceOf(Stream::class, $stream);
        $this->assertTrue($stream->isReadable());
        $this->assertTrue($stream->isWritable());
    }

    /**
     * Tests Phalcon\Http\Message\Stream\Temp :: __construct()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageStreamTempConstruct(): void
    {
        $stream = new Temp('r+b');

        $this->assertInstanceOf(Stream::class, $stream);
        $this->assertTrue($stream->isReadable());
        $this->assertTrue($stream->isWritable());
    }
}
