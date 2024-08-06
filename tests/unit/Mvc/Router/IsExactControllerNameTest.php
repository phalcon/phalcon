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

final class IsExactControllerNameTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Mvc\Router :: isExactControllerName()
     *
     * @author Sid Roberts <https://github.com/SidRoberts>
     * @since  2019-06-04
     */
    public function testMvcRouterIsExactControllerName(): void
    {
        $router = new Router(false);

        $this->assertTrue(
            $router->isExactControllerName()
        );
    }
}
