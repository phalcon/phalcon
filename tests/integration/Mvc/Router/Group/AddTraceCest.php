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

namespace Phalcon\Tests\Integration\Mvc\Router\Group;

use IntegrationTester;
use Phalcon\Mvc\Router\Group;
use Phalcon\Tests\Fixtures\Traits\RouterTrait;

class AddTraceCest
{
    use RouterTrait;

    /**
     * Tests Phalcon\Mvc\Router\Group :: addTrace()
     *
     * @author Sid Roberts <https://github.com/SidRoberts>
     * @since  2019-04-17
     */
    public function mvcRouterGroupAddTrace(IntegrationTester $I)
    {
        $I->wantToTest('Mvc\Router - addTrace()');

        $router = $this->getRouter(false);

        $group = new Group();

        $group->addTrace(
            '/docs/index',
            [
                'controller' => 'documentation10',
                'action'     => 'index',
            ]
        );

        $router->mount($group);


        $_SERVER['REQUEST_METHOD'] = 'TRACE';

        $router->handle('/docs/index');


        $I->assertSame(
            'documentation10',
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
