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
use Phalcon\Tests\UnitTestCase;

final class GetMethodTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Http\Message\ServerRequest :: getMethod()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function testHttpMessageServerRequestGetMethod(): void
    {
        $request = new ServerRequest('POST');

        $expected = 'POST';
        $actual   = $request->getMethod();
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Message\ServerRequest :: getMethod() - empty
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function testHttpMessageServerRequestGetMethodEmpty(): void
    {
        $request = new ServerRequest();

        $expected = 'GET';
        $actual   = $request->getMethod();
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Message\ServerRequest :: getMethod() - exception
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function testHttpMessageServerRequestGetMethodWxception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Invalid or unsupported method UNKNOWN'
        );

        (new ServerRequest('UNKNOWN'));
    }
}
