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

namespace Phalcon\Tests\Unit\Http\Message\ServerRequest;

use InvalidArgumentException;
use Phalcon\Http\Message\ServerRequest;
use Phalcon\Tests\Fixtures\Page\Http;
use Phalcon\Tests\AbstractUnitTestCase;

final class GetProtocolVersionTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Http\Message\ServerRequest :: getProtocolVersion()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-03-05
     */
    public function testHttpMessageServerRequestGetProtocolVersion(): void
    {
        $request = new ServerRequest(
            'GET',
            null,
            [],
            Http::STREAM,
            [],
            [],
            [],
            [],
            null,
            '2.0'
        );

        $expected = '2.0';
        $actual   = $request->getProtocolVersion();
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Message\ServerRequest :: getProtocolVersion() - empty
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-03-05
     */
    public function testHttpMessageServerRequestGetProtocolVersionEmpty(): void
    {
        $request = new ServerRequest();

        $expected = '1.1';
        $actual   = $request->getProtocolVersion();
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Message\ServerRequest :: getProtocolVersion() -
     * exception
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-03-05
     */
    public function testHttpMessageServerRequestGetProtocolVersionException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported protocol 1.2');

        (new ServerRequest(
            'GET',
            null,
            [],
            Http::STREAM,
            [],
            [],
            [],
            [],
            null,
            '1.2'
        ));
    }
}
