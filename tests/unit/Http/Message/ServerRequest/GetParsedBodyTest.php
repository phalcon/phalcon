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

final class GetParsedBodyTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Http\Message\ServerRequest :: getParsedBody()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-03-05
     */
    public function testHttpMessageServerRequestGetParsedBody(): void
    {
        $request = new ServerRequest(
            'GET',
            null,
            [],
            Http::STREAM,
            [],
            [],
            [],
            [],
            'something'
        );

        $expected = 'something';
        $actual   = $request->getParsedBody();
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Message\ServerRequest :: getParsedBody() - empty
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-03-05
     */
    public function testHttpMessageServerRequestGetParsedBodyEmpty(): void
    {
        $request = new ServerRequest();

        $actual = $request->getParsedBody();
        $this->assertNull($actual);
    }
}
