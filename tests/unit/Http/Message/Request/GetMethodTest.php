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

use InvalidArgumentException;
use Phalcon\Http\Message\Request;
use Phalcon\Tests\AbstractUnitTestCase;

final class GetMethodTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Http\Message\Request :: getMethod()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function testHttpMessageRequestGetMethod(): void
    {
        $request = new Request('POST');

        $this->assertSame(
            'POST',
            $request->getMethod()
        );
    }

    /**
     * Tests Phalcon\Http\Message\Request :: getMethod() - empty
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function testHttpMessageRequestGetMethodEmpty(): void
    {
        $request = new Request();

        $this->assertSame(
            'GET',
            $request->getMethod()
        );
    }

    /**
     * Tests Phalcon\Http\Message\Request :: getMethod() - exception
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function testHttpMessageRequestGetMethodWxception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Invalid or unsupported method UNKNOWN'
        );

        (new Request('UNKNOWN'));
    }
}
