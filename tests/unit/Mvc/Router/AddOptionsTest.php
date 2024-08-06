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

use Phalcon\Tests\UnitTestCase;
use Phalcon\Tests\Fixtures\Traits\RouterTrait;

final class AddOptionsTest extends UnitTestCase
{
    use RouterTrait;

    /**
     * Tests Phalcon\Mvc\Router :: addOptions()
     *
     * @author Sid Roberts <https://github.com/SidRoberts>
     * @since  2019-04-17
     */
    public function testMvcRouterAddOptions(): void
    {
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


        $this->assertSame(
            'documentation7',
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
