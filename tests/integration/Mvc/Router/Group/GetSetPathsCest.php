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

class GetSetPathsCest
{
    /**
     * Tests Phalcon\Mvc\Router\Group :: getPaths()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function mvcRouterGroupGetPaths(IntegrationTester $I)
    {
        $I->wantToTest('Mvc\Router\Group - getPaths()');

        $group = new Group();

        $actual = $group->getPaths();
        $I->assertNull($actual);

        $paths = [
            'one',
            'two',
        ];
        $group->setPaths($paths);
        $expected = $paths;
        $actual = $group->getPaths();
        $I->assertSame($expected, $actual);
    }
}
