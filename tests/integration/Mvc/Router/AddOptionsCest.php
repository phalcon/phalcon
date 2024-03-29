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

class AddOptionsCest
{
    use RouterTrait;

    /**
     * Tests Phalcon\Mvc\Router :: addOptions()
     *
     * @author Sid Roberts <https://github.com/SidRoberts>
     * @since  2019-04-17
     */
    public function mvcRouterAddOptions(IntegrationTester $I)
    {
        $I->wantToTest('Mvc\Router - addOptions()');

        $router = $this->getRouter(false);

        $router->addOptions(
            '/docs/index',
            [
                'controller' => 'documentation7',
                'action'     => 'index',
            ]
        );


        $_SERVER['REQUEST_METHOD'] = 'OPTIONS';

        $router->handle('/docs/index');


        $I->assertSame(
            'documentation7',
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
