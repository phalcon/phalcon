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

use function file_put_contents;
use function parse_str;

final class GetRawBodyTest extends AbstractHttpBase
{
    /**
     * Tests Phalcon\Http\Request :: getRawBody()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-03-17
     */
    public function testHttpRequestGetRawBody(): void
    {
        // Valid
        $this->registerStream();

        file_put_contents(Http::STREAM, 'fruit=orange&quantity=4');

        $request = $this->getRequestObject();

        $expected = [
            'fruit'    => 'orange',
            'quantity' => '4',
        ];

        $data = $request->getRawBody();
        parse_str($data, $actual);

        $this->assertSame($expected, $actual);

        $this->unregisterStream();
    }

    /**
     * Tests Phalcon\Http\Request :: getRawBody() - default
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-03-17
     */
    public function testHttpRequestGetRawBodyDefault(): void
    {
        // Empty
        $request = $this->getRequestObject();

        $actual = $request->getRawBody();
        $this->assertEmpty($actual);
    }
}
