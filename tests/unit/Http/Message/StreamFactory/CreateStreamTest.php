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

namespace Phalcon\Tests\Unit\Http\Message\StreamFactory;

use Phalcon\Http\Message\Factories\StreamFactory;
use Phalcon\Http\Message\Stream;
use Phalcon\Tests\AbstractUnitTestCase;

final class CreateStreamTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Http\Message\StreamFactory :: createStream()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function testHttpMessageStreamFactoryCreateStream(): void
    {
        $fileName = dataDir('assets/stream/mit.txt');
        $contents = file_get_contents($fileName);
        $factory  = new StreamFactory();
        $stream   = $factory->createStream($contents);

        $this->assertInstanceOf(
            Stream::class,
            $stream
        );

        $this->assertSame(
            $contents,
            $stream->getContents()
        );
    }
}
