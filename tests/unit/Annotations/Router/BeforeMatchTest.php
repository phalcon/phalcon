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

namespace Phalcon\Tests\Unit\Annotations\Router;

use Phalcon\Annotations\Router\Get;
use Phalcon\Annotations\Router\Route;
use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;

final class BeforeMatchTest extends AbstractUnitTestCase
{
    /**
     * The `Route` attribute and its HTTP-method subclasses declare the
     * `beforeMatch` argument that `Phalcon\Mvc\Router\Annotations` honors, so
     * the attribute can be instantiated with it (a string or array callable).
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-07-03
     */
    public function testAnnotationsRouterRouteBeforeMatch(): void
    {
        $route = new Route('/invoices', beforeMatch: 'Invoices::checkAccess');

        $this->assertSame('Invoices::checkAccess', $route->beforeMatch);

        // The HTTP-method shortcuts forward the argument to the parent Route.
        $get = new Get(
            '/invoices',
            beforeMatch: ['Invoices', 'checkAccess']
        );

        $this->assertSame(['Invoices', 'checkAccess'], $get->beforeMatch);
        $this->assertSame('GET', $get->methods);
    }
}
