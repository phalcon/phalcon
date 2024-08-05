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

namespace Phalcon\Tests\Unit\Http\Request;

use Phalcon\Tests\Unit\Http\Helper\HttpBase;

use function uniqid;

final class GetHTTPRefererTest extends HttpBase
{
    /**
     * Tests Phalcon\Http\Request :: getHTTPReferer()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-03-17
     */
    public function testHttpRequestGetHTTPReferer(): void
    {
        $referrer                = uniqid('ref-');
        $_SERVER['HTTP_REFERER'] = $referrer;

        $request = $this->getRequestObject();

        $expected = $referrer;
        $actual   = $request->getHTTPReferer();
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Request :: getHTTPReferer() - empty
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-03-17
     */
    public function testHttpRequestGetHTTPRefererEmpty(): void
    {
        $request = $this->getRequestObject();

        $actual = $request->getHTTPReferer();
        $this->assertEmpty($actual);
    }
}
