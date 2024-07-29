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

namespace Phalcon\Tests\Integration\Mvc\View;

use IntegrationTester;
use Phalcon\Di\Di;
use Phalcon\Mvc\View;

use function dataDir;

class HasCest
{
    /**
     * Tests Phalcon\Mvc\View :: exists()
     *
     * @author Kamil Skowron <git@hedonsoftware.com>
     * @since  2014-05-28
     */
    public function mvcViewHas(IntegrationTester $I)
    {
        $I->wantToTest('Mvc\View - has()');

        $container = new Di();

        $view = new View();

        $view->setViewsDir(
            $I->getDirSeparator(dataDir('fixtures/views'))
        );

        $view->setDI($container);

        $I->assertTrue(
            $view->has('currentrender/query')
        );

        $I->assertTrue(
            $view->has('currentrender/yup')
        );

        $I->assertFalse(
            $view->has('currentrender/nope')
        );

        $I->assertTrue(
            $view->exists('currentrender/query')
        );

        $I->assertTrue(
            $view->exists('currentrender/yup')
        );

        $I->assertFalse(
            $view->exists('currentrender/nope')
        );
    }
}
