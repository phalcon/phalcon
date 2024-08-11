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

use Phalcon\Http\Message\Exception\InvalidArgumentException;
use Phalcon\Http\Message\Request;
use Phalcon\Tests\AbstractUnitTestCase;

final class WithProtocolVersionTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Http\Message\Request :: withProtocolVersion()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function testHttpMessageRequestWithProtocolVersion(): void
    {
        $request     = new Request();
        $newInstance = $request->withProtocolVersion('2.0');

        $this->assertNotSame($request, $newInstance);

        $this->assertSame(
            '1.1',
            $request->getProtocolVersion()
        );

        $this->assertSame(
            '2.0',
            $newInstance->getProtocolVersion()
        );
    }

    /**
     * Tests Phalcon\Http\Message\Request :: withProtocolVersion() -
     * unsupported protocol
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function testHttpMessageRequestWithProtocolVersionEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid protocol value');

        $request = new Request();
        $request->withProtocolVersion('');
    }

    /**
     * Tests Phalcon\Http\Message\Request :: withProtocolVersion() - empty
     * protocol
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function testHttpMessageRequestWithProtocolVersionUnsupported(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported protocol 4.0');

        $request = new Request();
        $request->withProtocolVersion('4.0');
    }
}
