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

final class SeekTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Http\Message\Stream :: seek()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function testHttpMessageStreamSeek(): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $this->markTestSkipped('Need to fix Windows new lines...');
        }

        $fileName = dataDir('assets/stream/mit.txt');
        $stream   = new Stream($fileName, 'rb');

        $stream->seek(64);
        $expected = 'Permission is hereby granted, free of charge, to any '
            . 'person obtaining a copy of this software and associated '
            . 'documentation files (the "Software"), to deal in the '
            . 'Software without restriction, including without limitation '
            . 'the rights to use, copy, modify, merge, publish, distribute, '
            . 'sublicense, and/or sell copies of the Software, and to permit '
            . 'persons to whom the Software is furnished to do so, subject '
            . 'to the following conditions:';
        $actual   = $stream->read(432);
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Message\Stream :: seek() - after file size
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function testHttpMessageStreamSeekAfterFileSize(): void
    {
        $fileName = dataDir('assets/stream/mit.txt');
        $stream   = new Stream($fileName, 'rb');

        $stream->seek(10240);
        $expected = '';
        $actual   = $stream->read(1);
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Message\Stream :: seek() - exception
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function testHttpMessageStreamSeekException(): void
    {
        $fileName = dataDir('assets/stream/mit.txt');
        $stream   = new StreamFixture($fileName, 'rb');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The resource is not seekable.');

        $stream->seek(10240);
    }
}
