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

use Phalcon\Di\FactoryDefault;
use Phalcon\Tests\AbstractUnitTestCase;
use Phalcon\Tests\Fixtures\Mvc\RouterFixture;
use Phalcon\Tests\Fixtures\Traits\RouterTrait;

final class ExtractRealUriTest extends AbstractUnitTestCase
{
    use RouterTrait;

    /**
     * Tests Phalcon\Mvc\Router :: extractRealUri()
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2025-04-11
     * @issue        16749
     */
    public function testExtractRealUri(): void
    {
        $router = new RouterFixture(false);
        $router->setDI(new FactoryDefault());

        $expected = '/admin/private/businesses/list/my/123';
        $actual   = $router->protectedExtractRealUri(
            '/admin/private/businesses/list/my/123?query=string'
        );
        $this->assertSame($expected, $actual);

        $expected = '/admin/private/businesses/list/my/123';
        $actual = $router->protectedExtractRealUri(
            '/admin/private/businesses/list/my/123'
        );
        $this->assertSame($expected, $actual);
    }
}
