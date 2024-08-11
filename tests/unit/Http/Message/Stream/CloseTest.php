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

use function is_resource;

final class CloseTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Http\Message\Stream :: close()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function testHttpMessageStreamClose(): void
    {
        $fileName = dataDir('assets/stream/mit.txt');
        $handle   = fopen($fileName, 'rb');
        $stream   = new Stream($handle);

        $stream->close();

        $this->assertFalse(is_resource($handle));
    }

    /**
     * Tests Phalcon\Http\Message\Stream :: close() - detach
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function testHttpMessageStreamCloseDetach(): void
    {
        $fileName = dataDir('assets/stream/mit.txt');
        $handle   = fopen($fileName, 'rb');
        $stream   = new Stream($handle);

        $stream->close();

        $this->assertNull($stream->detach());
    }
}
