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

namespace Phalcon\Tests\Unit\Cli\Router\Route;

use Phalcon\Cli\Router\Route;
use Phalcon\Tests\UnitTestCase;

final class GetCompiledPatternTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Cli\Router\Route :: getCompiledPattern()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-01-05
     */
    public function testCliRouterRouteGetCompiledPattern(): void
    {
        Route::reset();
        Route::delimiter('/');
        $route = new Route(
            '/:module/:namespace/:task/:action/:params/:delimiter'
        );

        $expected = '#^/([a-zA-Z0-9\_\-]+)/([a-zA-Z0-9\_\-]+)/'
            . '([a-zA-Z0-9\_\-]+)/([a-zA-Z0-9\_\-]+)(/.*)*//$#';

        $actual = $route->getCompiledPattern();
        $this->assertSame($expected, $actual);
    }
}
