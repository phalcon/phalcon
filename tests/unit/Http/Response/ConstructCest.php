<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Http\Response;

use Page\Http;
use Phalcon\Di\InjectionAwareInterface;
use Phalcon\Events\EventsAwareInterface;
use Phalcon\Http\Message\Interfaces\ResponseStatusCodeInterface;
use Phalcon\Http\Response;
use Phalcon\Http\ResponseInterface;
use UnitTester;

class ConstructCest
{
    /**
     * Tests Phalcon\Http\Response :: __construct()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-12-08
     */
    public function httpResponseConstruct(UnitTester $I)
    {
        $I->wantToTest('Http\Response - __construct()');

        $response = new Response();

        $class = Response::class;
        $I->assertInstanceOf($class, $response);
        $class = ResponseInterface::class;
        $I->assertInstanceOf($class, $response);
        $class = InjectionAwareInterface::class;
        $I->assertInstanceOf($class, $response);
        $class = EventsAwareInterface::class;
        $I->assertInstanceOf($class, $response);
        $class = ResponseStatusCodeInterface::class;
        $I->assertInstanceOf($class, $response);
    }

    /**
     * Tests Phalcon\Http\Response :: __construct(content = null)
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-12-08
     */
    public function httpResponseConstructWithContent(UnitTester $I)
    {
        $I->wantToTest('Http\Response - __construct(content = null)');

        $content  = Http::TEST_CONTENT;
        $response = new Response($content);

        $expected = $content;
        $actual   = $response->getContent();
        $I->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Response :: __construct(content = null, code = null)
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-12-08
     */
    public function httpResponseConstructWithContentCode(UnitTester $I)
    {
        $I->wantToTest(
            'Http\Response - __construct(content = null, code = null)'
        );

        $content = Http::TEST_CONTENT;
        $code    = Http::CODE_200;

        $response = new Response($content, $code);

        $expected = $content;
        $actual   = $response->getContent();
        $I->assertSame($expected, $actual);

        $expected = $code;
        $actual   = $response->getStatusCode();
        $I->assertSame($expected, $actual);

        // Check Status message
        $expected = Http::MESSAGE_200_OK;
        $actual   = $response->getHeaders()->get(Http::STATUS);
        $I->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Response :: __construct(content = null, code = null,
     * status = null)
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-12-08
     */
    public function httpResponseConstructWithContentCodeStatus(UnitTester $I)
    {
        $I->wantToTest(
            'Http\Response - __construct(content = null, code = null, status = null)'
        );

        $content = Http::TEST_CONTENT;
        $code    = Http::CODE_200;

        $response = new Response($content, $code, 'Success');

        $expected = $content;
        $actual   = $response->getContent();
        $I->assertSame($expected, $actual);

        $expected = $code;
        $actual   = $response->getStatusCode();
        $I->assertSame($expected, $actual);

        // Check Status message
        $expected = Http::MESSAGE_200_SUCCESS;
        $actual   = $response->getHeaders()->get(Http::STATUS);
        $I->assertSame($expected, $actual);
    }
}
