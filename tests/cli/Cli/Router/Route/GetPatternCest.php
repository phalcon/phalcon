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

class GetPatternCest
{
    /**
     * Tests Phalcon\Cli\Router\Route :: getPattern()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-01-05
     */
    public function cliRouterRouteGetPattern(CliTester $I)
    {
        $I->wantToTest('Cli\Router\Route - getPattern()');

        Route::reset();
        Route::delimiter('/');
        $pattern = '/:module/:namespace/:task/:action/:params/:delimiter';
        $route   = new Route($pattern);

        $expected = $pattern;
        $actual   = $route->getPattern();
        $I->assertSame($expected, $actual);
    }
}
