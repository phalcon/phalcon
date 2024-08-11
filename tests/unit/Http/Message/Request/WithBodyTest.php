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

namespace Phalcon\Tests\Unit\Http\Message\Request;

use Phalcon\Http\Message\Request;
use Phalcon\Http\Message\Stream;
use Phalcon\Tests\AbstractUnitTestCase;

final class WithBodyTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Http\Message\Request :: withBody()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function testHttpMessageRequestWithBody(): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $this->markTestSkipped('Need to fix Windows new lines...');
        }

        $fileName = dataDir('/assets/stream/mit.txt');
        $stream   = new Stream($fileName, 'rb');
        $request  = new Request();

        $newInstance = $request->withBody($stream);

        $this->assertNotSame($request, $newInstance);

        $this->assertFileContentsEqual(
            $fileName,
            (string)$newInstance->getBody()
        );
    }
}
