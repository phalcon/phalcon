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

final class WithCookieParamsTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Http\Message\ServerRequest :: withCookieParams()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-03-03
     */
    public function testHttpMessageServerRequestWithCookieParams(): void
    {
        $request     = new ServerRequest();
        $newInstance = $request->withCookieParams(
            [
                'one' => 'two',
            ]
        );

        $this->assertNotSame($request, $newInstance);

        $this->assertSame(
            [
                'one' => 'two',
            ],
            $newInstance->getCookieParams()
        );
    }
}
