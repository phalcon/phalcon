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
use Phalcon\Tests\Fixtures\Page\Http;
use Phalcon\Tests\AbstractUnitTestCase;

final class HasHeaderTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Http\Message\Request :: hasHeader()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function testHttpMessageRequestHasHeader(): void
    {
        $data = [
            'Accept' => [
                Http::CONTENT_TYPE_HTML,
                'text/json',
            ],
        ];

        $request = new Request('GET', null, Http::STREAM, $data);

        $this->assertTrue(
            $request->hasHeader('accept')
        );

        $this->assertTrue(
            $request->hasHeader('aCCepT')
        );
    }

    /**
     * Tests Phalcon\Http\Message\Request :: hasHeader() - empty
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function testHttpMessageRequestHasHeaderEmpty(): void
    {
        $request = new Request();

        $this->assertFalse(
            $request->hasHeader('empty')
        );
    }
}
