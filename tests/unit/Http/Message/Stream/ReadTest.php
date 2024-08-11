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

final class ReadTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Http\Message\Stream :: read()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function testHttpMessageStreamRead(): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $this->markTestSkipped('Need to fix Windows new lines...');
        }

        $fileName = dataDir('assets/stream/mit.txt');

        $stream = new Stream($fileName, 'rb');

        $expected = 'The MIT License (MIT)' . PHP_EOL . PHP_EOL . 'Copyright (c) 2015-present, Phalcon PHP';
        $actual   = $stream->read(62);
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Message\Stream :: read() - detached
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function testHttpMessageStreamReadDetached(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('A valid resource is required.');

        $fileName = dataDir('assets/stream/mit.txt');
        $stream   = new Stream($fileName, 'rb');
        $stream->detach();
        $stream->read(10);
    }
}
