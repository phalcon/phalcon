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

use Phalcon\Mvc\Router;
use Phalcon\Tests\UnitTestCase;

final class SetDefaultControllerTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Mvc\Router :: setDefaultController()
     *
     * @author Sid Roberts <https://github.com/SidRoberts>
     * @since  2019-05-22
     */
    public function testMvcRouterSetDefaultController(): void
    {
        $router = new Router();

        $router->setDefaultController('main');

        $this->assertSame(
            'main',
            $router->getDefaults()['controller']
        );
    }
}
