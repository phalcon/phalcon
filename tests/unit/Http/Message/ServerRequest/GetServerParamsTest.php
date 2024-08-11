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

final class GetServerParamsTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Http\Message\ServerRequest :: getServerParams()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function testHttpMessageServerRequestGetServerParams(): void
    {
        $params  = ['one' => 'two'];
        $request = new ServerRequest('GET', null, $params);

        $expected = $params;
        $actual   = $request->getServerParams();
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Message\ServerRequest :: getServerParams() - empty
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function testHttpMessageServerRequestGetServerParamsEmpty(): void
    {
        $request = new ServerRequest();

        $actual = $request->getServerParams();
        $this->assertEmpty($actual);
    }
}
