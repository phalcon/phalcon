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

namespace Phalcon\Tests\Integration\Mvc\Router;

use IntegrationTester;
use Phalcon\Tests\Fixtures\Traits\RouterTrait;

class AddPutCest
{
    use RouterTrait;

    /**
     * Tests Phalcon\Mvc\Router :: addPut()
     *
     * @author Sid Roberts <https://github.com/SidRoberts>
     * @since  2019-04-17
     */
    public function mvcRouterAddPut(IntegrationTester $I)
    {
        $I->wantToTest('Mvc\Router - addPut()');

        $router = $this->getRouter(false);

        $router->addPut(
            '/docs/index',
            [
                'controller' => 'documentation5',
                'action'     => 'index',
            ]
        );


        $_SERVER['REQUEST_METHOD'] = 'PUT';

        $router->handle('/docs/index');


        $I->assertSame(
            'documentation5',
            $router->getControllerName()
        );

        $I->assertSame(
            'index',
            $router->getActionName()
        );

        $I->assertSame(
            [],
            $router->getParams()
        );
    }
}
