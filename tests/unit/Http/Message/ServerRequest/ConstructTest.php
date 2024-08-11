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

use Phalcon\Http\Message\Interfaces\ServerRequestInterface;
use Phalcon\Http\Message\ServerRequest;
use Phalcon\Http\Message\Uri;
use Phalcon\Tests\Fixtures\Page\Http;
use Phalcon\Tests\AbstractUnitTestCase;

final class ConstructTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Http\Message\ServerRequest :: __construct()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-08
     */
    public function testHttpMessageServerRequestConstruct(): void
    {
        $request = new ServerRequest();

        $this->assertInstanceOf(
            ServerRequestInterface::class,
            $request
        );
    }

    /**
     * Tests Phalcon\Http\Message\ServerRequest :: __construct()
     *
     * @author cq-z <64899484@qq.com>
     * @since  2019-06-02
     */
    public function testHttpMessageServerRequestConstructIssues14151(): void
    {
        $request = new ServerRequest(
            'GET',
            new Uri(),
            [],
            Http::STREAM,
            [
                'host' => ['127.0.0.1'],
            ]
        );

        $expected = ['127.0.0.1'];

        $this->assertSame(
            $expected,
            $request->getHeader('host')
        );
    }
}
