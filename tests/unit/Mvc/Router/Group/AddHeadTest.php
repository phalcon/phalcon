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

namespace Phalcon\Tests\Unit\Mvc\Router\Group;

use Phalcon\Mvc\Router\Group;
use Phalcon\Tests\Fixtures\Traits\RouterTrait;
use Phalcon\Tests\AbstractUnitTestCase;

final class AddHeadTest extends AbstractUnitTestCase
{
    use RouterTrait;

    /**
     * Tests Phalcon\Mvc\Router\Group :: addHead()
     *
     * @author Sid Roberts <https://github.com/SidRoberts>
     * @since  2019-04-17
     */
    public function testMvcRouterGroupAddHead(): void
    {
        $router = $this->getRouter(false);

        $group = new Group();

        $group->addHead(
            '/docs/index',
            [
                'controller' => 'documentation8',
                'action'     => 'index',
            ]
        );

        $router->mount($group);


        $_SERVER['REQUEST_METHOD'] = 'HEAD';

        $router->handle('/docs/index');


        $this->assertSame(
            'documentation8',
            $router->getControllerName()
        );

        $this->assertSame(
            'index',
            $router->getActionName()
        );

        $this->assertSame(
            [],
            $router->getParams()
        );
    }
}
