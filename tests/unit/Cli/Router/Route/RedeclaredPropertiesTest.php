<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Cli\Router\Route;

use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;
use Phalcon\Tests\Unit\Cli\Router\Route\Fake\FakeRouteRedeclared;

final class RedeclaredPropertiesTest extends AbstractUnitTestCase
{
    /**
     * A route can declare the delimiter with the cphalcon type.
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     *
     * @issue  https://github.com/phalcon/phalcon/issues/840
     */
    public function testCliRouterRouteRedeclaredDelimiter(): void
    {
        $route = new FakeRouteRedeclared('main');

        $this->assertSame('main', $route->getPattern());
    }
}
