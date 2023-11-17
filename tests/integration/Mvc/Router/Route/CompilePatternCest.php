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

namespace Phalcon\Tests\Integration\Mvc\Router\Route;

use IntegrationTester;
use Phalcon\Mvc\Router\Route;

/**
 * Class CompilePatternCest
 */
class CompilePatternCest
{
    /**
     * Tests Phalcon\Mvc\Router\Route :: compilePattern()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-10-05
     */
    public function mvcRouterRouteCompilePattern(IntegrationTester $I)
    {
        $I->wantToTest('Mvc\Router\Route - compilePattern()');

        $route = '/my-simple-route';
        $simpleRoute = new Route($route);

        $expected = $route;
        $actual   = $simpleRoute->getCompiledPattern();
        $I->assertSame($expected, $actual);

        /**
         * Placeholder
         */
        $route = '/:module/:namespace/:controller/:action/:params/:int';
        $placeholderRoute = new Route($route);

        $expected = '#^/([\\w0-9\\_\\-]+)/([\\w0-9\\_\\-]+)/([\\w0-9\\_\\-]+)/([\\w0-9\\_\\-]+)(/.*)*/([0-9]+)$#u';
        $actual   = $placeholderRoute->getCompiledPattern();
        $I->assertSame($expected, $actual);

        /**
         * Custom regex
         */
        $route = '/([\\w0-9\\_\\-]+)/([\\w0-9\\_\\-]+)/([\\w0-9\\_\\-]+)/([\\w0-9\\_\\-]+)(/.*)*/([0-9]+)';
        $regexRoute = new Route($route);

        $expected = '#^/([\\w0-9\\_\\-]+)/([\\w0-9\\_\\-]+)/([\\w0-9\\_\\-]+)/([\\w0-9\\_\\-]+)(/.*)*/([0-9]+)$#u';
        $actual   = $regexRoute->getCompiledPattern();
        $I->assertSame($expected, $actual);
    }
}
