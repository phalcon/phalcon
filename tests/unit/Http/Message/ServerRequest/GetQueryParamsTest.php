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
use Phalcon\Tests\Fixtures\Page\Http;
use Phalcon\Tests\AbstractUnitTestCase;

final class GetQueryParamsTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Http\Message\ServerRequest :: getQueryParams()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-03-03
     */
    public function testHttpMessageServerRequestGetQueryParams(): void
    {
        $params  = ['one' => 'two'];
        $request = new ServerRequest(
            'GET',
            null,
            [],
            Http::STREAM,
            [],
            [],
            $params
        );

        $expected = $params;
        $actual   = $request->getQueryParams();
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Message\ServerRequest :: getQueryParams() - empty
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-03-03
     */
    public function testHttpMessageServerRequestGetQueryParamsEmpty(): void
    {
        $request = new ServerRequest();

        $actual = $request->getQueryParams();
        $this->assertEmpty($actual);
    }
}
