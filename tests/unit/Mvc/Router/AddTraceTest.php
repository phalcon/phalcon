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

namespace Phalcon\Tests\Unit\Mvc\Router;

use Phalcon\Tests\Fixtures\Traits\RouterTrait;
use Phalcon\Tests\UnitTestCase;

final class AddTraceTest extends UnitTestCase
{
    use RouterTrait;

    /**
     * Tests Phalcon\Mvc\Router :: addTrace()
     *
     * @author Sid Roberts <https://github.com/SidRoberts>
     * @since  2019-04-17
     */
    public function testMvcRouterAddTrace(): void
    {
        $router = $this->getRouter(false);

        $router->addTrace(
            '/docs/index',
            [
                'controller' => 'documentation10',
                'action'     => 'index',
            ]
        );


        $_SERVER['REQUEST_METHOD'] = 'TRACE';

        $router->handle('/docs/index');


        $this->assertSame(
            'documentation10',
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
