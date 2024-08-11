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
use Phalcon\Tests\AbstractUnitTestCase;
use RuntimeException;

use function logsDir;

final class WriteTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Http\Message\Stream :: write()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function testHttpMessageStreamWrite(): void
    {
        $fileName = $this->getNewFileName();
        $fileName = logsDir($fileName);
        $stream   = new Stream($fileName, 'wb');

        $source   = 'A well regulated Militia, being necessary to the security of a free State, '
            . 'the right of the people to keep and bear Arms, shall not be infringed.';
        $expected = strlen($source);
        $actual   = $stream->write($source);
        $this->assertSame($expected, $actual);

        $stream->close();

        $stream   = new Stream($fileName, 'rb');
        $expected = $source;
        $actual   = $stream->getContents();
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Message\Stream :: write() - detached
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function testHttpMessageStreamWriteDetached(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('A valid resource is required.');

        $fileName = $this->getNewFileName();
        $fileName = logsDir($fileName);
        $stream   = new Stream($fileName, 'wb');
        $stream->detach();

        $stream->write('abc');
    }

    /**
     * Tests Phalcon\Http\Message\Stream :: write() - exception not writable
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function testHttpMessageStreamWriteNotWritable(): void
    {
        $fileName = $this->getNewFileName();
        $fileName = logsDir($fileName);
        $stream   = new StreamFixture($fileName, 'wb');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The resource is not writable.');

        $stream->write('abc');
    }
}
