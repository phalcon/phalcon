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
use Phalcon\Mvc\Router;

final class SetDefaultModuleTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Mvc\Router :: setDefaultModule()
     *
     * @author Sid Roberts <https://github.com/SidRoberts>
     * @since  2019-05-22
     */
    public function testMvcRouterSetDefaultModule(): void
    {
        $router = new Router();

        $router->setDefaultModule('front');

        $this->assertSame(
            'front',
            $router->getDefaults()['module']
        );
    }
}
