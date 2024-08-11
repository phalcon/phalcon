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

final class WithBodyTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Http\Message\Response :: withBody()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-03-09
     */
    public function testHttpMessageResponseWithBody(): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $this->markTestSkipped('Need to fix Windows new lines...');
        }

        $fileName = dataDir('/assets/stream/mit.txt');
        $stream   = new Stream($fileName, 'rb');
        $response = new Response();

        $newInstance = $response->withBody($stream);

        $this->assertNotSame($response, $newInstance);

        $this->assertFileContentsEqual(
            $fileName,
            (string)$newInstance->getBody()
        );
    }
}
