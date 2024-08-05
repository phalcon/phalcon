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

final class GetSchemeTest extends HttpBase
{
    /**
     * Tests getScheme with HTTPS
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-10-04
     */
    public function testHttpRequestGetScheme(): void
    {
        $request = $this->getRequestObject();

        $_SERVER['HTTPS'] = 'on';
        $actual           = $request->getScheme();

        $this->assertSame('https', $actual);
    }

    /**
     * Tests getScheme default
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-10-04
     */
    public function testHttpRequestGetSchemeDefault(): void
    {
        $request = $this->getRequestObject();

        $expected = 'http';
        $actual   = $request->getScheme();
        $this->assertSame($expected, $actual);
    }
}
