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
use Phalcon\Tests\AbstractUnitTestCase;

final class GetRoutesTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Mvc\Router\Group :: getRoutes()
     *
     * @author Sid Roberts <https://github.com/SidRoberts>
     * @since  2019-06-01
     */
    public function testMvcRouterGroupGetRoutes(): void
    {
        $group = new Group();

        $getRoute = $group->addGet(
            '/docs/index',
            [
                'controller' => 'documentation4',
                'action'     => 'index',
            ]
        );

        $postRoute = $group->addPost(
            '/docs/index',
            [
                'controller' => 'documentation3',
                'action'     => 'index',
            ]
        );

        $this->assertCount(
            2,
            $group->getRoutes()
        );

        $this->assertSame(
            [
                $getRoute,
                $postRoute,
            ],
            $group->getRoutes()
        );
    }
}
