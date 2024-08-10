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

use Phalcon\Tests\Fixtures\Page\Http;
use Phalcon\Tests\Unit\Http\Helper\AbstractHttpBase;

final class GetServerNameTest extends AbstractHttpBase
{
    /**
     * Tests Phalcon\Http\Request :: getServerName()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-03-17
     */
    public function testHttpRequestGetServerName(): void
    {
        $_SERVER['SERVER_NAME'] = Http::TEST_DOMAIN;

        $request = $this->getRequestObject();

        $expected = Http::TEST_DOMAIN;
        $actual   = $request->getServerName();
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Request :: getServerName() - default
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-03-17
     */
    public function testHttpRequestGetServerNameDefault(): void
    {
        $request = $this->getRequestObject();

        $expected = Http::HOST_LOCALHOST;
        $actual   = $request->getServerName();
        $this->assertSame($expected, $actual);
    }
}
