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

class AddPatchCest
{
    use RouterTrait;

    /**
     * Tests Phalcon\Mvc\Router :: addPatch()
     *
     * @author Sid Roberts <https://github.com/SidRoberts>
     * @since  2019-04-17
     */
    public function mvcRouterAddPatch(IntegrationTester $I)
    {
        $I->wantToTest('Mvc\Router - addPatch()');

        $router = $this->getRouter(false);

        $router->addPatch(
            '/docs/index',
            [
                'controller' => 'documentation4',
                'action'     => 'index',
            ]
        );


        $_SERVER['REQUEST_METHOD'] = 'PATCH';

        $router->handle('/docs/index');


        $I->assertSame(
            'documentation4',
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
