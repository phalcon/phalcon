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
use Phalcon\Tests\AbstractUnitTestCase;

final class WithMethodTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Http\Message\Request :: withMethod()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function testHttpMessageRequestWithMethod(): void
    {
        $request = new Request();

        $newInstance = $request->withMethod('POST');

        $this->assertNotSame($request, $newInstance);

        $this->assertSame(
            'GET',
            $request->getMethod()
        );

        $this->assertSame(
            'POST',
            $newInstance->getMethod()
        );
    }
}
