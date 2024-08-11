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

use Phalcon\Http\Message\ServerRequest;
use Phalcon\Tests\AbstractUnitTestCase;

final class WithProtocolVersionTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Http\Message\ServerRequest :: withProtocolVersion()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function testHttpMessageServerRequestWithProtocolVersion(): void
    {
        $request     = new ServerRequest();
        $newInstance = $request->withProtocolVersion('2.0');

        $this->assertNotSame($request, $newInstance);

        $expected = '1.1';
        $actual   = $request->getProtocolVersion();
        $this->assertSame($expected, $actual);

        $expected = '2.0';
        $actual   = $newInstance->getProtocolVersion();
        $this->assertSame($expected, $actual);
    }
}
