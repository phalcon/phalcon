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

final class WithRequestTargetTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Http\Message\ServerRequest :: withRequestTarget()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function testHttpMessageServerRequestWithRequestTarget(): void
    {
        $request     = new ServerRequest();
        $newInstance = $request->withRequestTarget('/test');

        $this->assertNotSame($request, $newInstance);

        $expected = '/';
        $actual   = $request->getRequestTarget();
        $this->assertSame($expected, $actual);

        $expected = '/test';
        $actual   = $newInstance->getRequestTarget();
        $this->assertSame($expected, $actual);
    }
}
