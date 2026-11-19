<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Cli\Router\Route;

use Phalcon\Cli\Router\Route;
use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;
use TypeError;

final class GetDelimiterTest extends AbstractUnitTestCase
{
    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-01-05
     */
    public function testCliRouterRouteGetDelimiter(): void
    {
        $store = Route::getDelimiter();

        Route::delimiter(Route::DEFAULT_DELIMITER);
        $route = new Route("test");

        // Default delimiter
        $expected = " ";
        $actual   = Route::DEFAULT_DELIMITER;
        $this->assertSame($expected, $actual);

        $expected = " ";
        $actual   = $route->getDelimiter();
        $this->assertSame($expected, $actual);

        $route = new Route("test");
        $route::delimiter("-");

        $expected = " ";
        $actual   = Route::DEFAULT_DELIMITER;
        $this->assertSame($expected, $actual);

        $expected = "-";
        $actual   = $route->getDelimiter();
        $this->assertSame($expected, $actual);

        $route = new Route("test");
        $route::delimiter("-");

        Route::delimiter($store);
    }

    /**
     * The delimiter of a route is a string, thus a null delimiter gives a
     * TypeError, as in cphalcon.
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     *
     * @issue  https://github.com/phalcon/phalcon/issues/840
     */
    public function testCliRouterRouteGetDelimiterNull(): void
    {
        $store = Route::getDelimiter();

        Route::delimiter(null);

        $this->expectException(TypeError::class);
        $this->expectExceptionMessage(
            'Cannot assign null to property '
            . 'Phalcon\Cli\Router\Route::$delimiter of type string'
        );

        try {
            new Route("test");
        } finally {
            Route::delimiter($store);
        }
    }
}
