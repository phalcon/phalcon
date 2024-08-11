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

namespace Phalcon\Tests\Unit\Http\Message\Response;

use Phalcon\Http\Message\Response;
use Phalcon\Http\Message\Stream;
use Phalcon\Tests\AbstractUnitTestCase;

final class GetBodyTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Http\Message\Response :: getBody()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-03-09
     */
    public function testHttpMessageResponseGetBody(): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $this->markTestSkipped('Need to fix Windows new lines...');
        }

        $fileName = dataDir('/assets/stream/mit.txt');
        $stream   = new Stream($fileName, 'rb');
        $response = new Response($stream);

        $this->assertFileContentsEqual(
            $fileName,
            (string)$response->getBody()
        );
    }

    /**
     * Tests Phalcon\Http\Message\Response :: getBody() - empty
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-03-09
     */
    public function testHttpMessageResponseGetBodyEmpty(): void
    {
        $response = new Response();

        $this->assertInstanceOf(
            Stream::class,
            $response->getBody()
        );
    }
}
