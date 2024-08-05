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

use Codeception\Example;
use Phalcon\Tests\UnitTestCase;
use Phalcon\Tests\Fixtures\Traits\RouterTrait;

final class RemoveExtraSlashesTest extends UnitTestCase
{
    use RouterTrait;

    /**
     * Tests removing extra slashes
     *
     * @author       Andy Gutierrez <andres.gutierrez@phalcon.io>
     * @since        2012-12-16
     *
     * @dataProvider getMatchingWithExtraSlashes
     */
    public function testRemovingExtraSlashes(
        string $route,
        array $params
    ): void {
        $router = $this->getRouter();

        $router->removeExtraSlashes(true);

        $router->handle($route);

        $actual = $router->wasMatched();
        $this->assertTrue($actual);

        $expected = $params['controller'];
        $actual   = $router->getControllerName();
        $this->assertSame($expected, $actual);

        $expected = $params['action'];
        $actual   = $router->getActionName();
        $this->assertSame($expected, $actual);
    }

    /**
     * @return array[]
     */
    public static function getMatchingWithExtraSlashes(): array
    {
        return [
            [
                '/index/',
                [
                    'controller' => 'index',
                    'action'     => '',
                ],
            ],

            [
                '/session/start/',
                [
                    'controller' => 'session',
                    'action'     => 'start',
                ],
            ],

            [
                '/users/edit/100/',
                [
                    'controller' => 'users',
                    'action'     => 'edit',
                ],
            ],
        ];
    }
}
