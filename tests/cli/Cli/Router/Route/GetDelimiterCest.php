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

namespace Phalcon\Tests\Cli\Cli\Router\Route;

use CliTester;
use Phalcon\Cli\Router\Route;

class GetDelimiterCest
{
    /**
     * Tests Phalcon\Cli\Router\Route :: getDelimiter()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-01-05
     */
    public function cliRouterRouteGetDelimiter(CliTester $I)
    {
        $I->wantToTest('Cli\Router\Route - getDelimiter()');

        $store = Route::getDelimiter();

        Route::delimiter(Route::DEFAULT_DELIMITER);
        $route = new Route("test");

        // Default delimiter
        $expected = " ";
        $actual   = Route::DEFAULT_DELIMITER;
        $I->assertSame($expected, $actual);

        $expected = " ";
        $actual   = $route->getDelimiter();
        $I->assertSame($expected, $actual);

        $route = new Route("test");
        $route::delimiter("-");

        $expected = " ";
        $actual   = Route::DEFAULT_DELIMITER;
        $I->assertSame($expected, $actual);

        $expected = "-";
        $actual   = $route->getDelimiter();
        $I->assertSame($expected, $actual);

        $route = new Route("test");
        $route::delimiter("-");

        Route::delimiter($store);
    }
}
