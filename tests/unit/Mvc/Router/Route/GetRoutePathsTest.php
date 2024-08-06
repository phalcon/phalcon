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

namespace Phalcon\Tests\Unit\Mvc\Router\Route;

use Phalcon\Tests\UnitTestCase;
use Phalcon\Mvc\Router\Route;

final class GetRoutePathsTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Mvc\Router\Route :: getRoutePaths()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function testMvcRouterRouteGetRoutePaths(): void
    {
        $arrayDefinition  = ["controller" => 'FooBar', "action" => 'baz'];
        $stringDefinition = "FooBar::baz";

        $this->assertSame($arrayDefinition, Route::getRoutePaths($arrayDefinition));
        $this->assertSame($arrayDefinition, Route::getRoutePaths($stringDefinition));
    }
}
