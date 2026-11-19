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

namespace Phalcon\Tests\Unit\Mvc\Router\Annotations;

use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;
use Phalcon\Tests\Unit\Mvc\Fake\FakeRouterAnnotationsOverride;

final class OverrideSignatureTest extends AbstractUnitTestCase
{
    /**
     * A router can override the setters with the cphalcon signatures.
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     *
     * @issue  https://github.com/phalcon/phalcon/issues/841
     */
    public function testMvcRouterAnnotationsSettersOverride(): void
    {
        $router = new FakeRouterAnnotationsOverride(false);

        $this->assertSame($router, $router->setActionPreformatCallback(null));
        $this->assertSame($router, $router->setActionSuffix('Act'));
        $this->assertSame($router, $router->setControllerSuffix('Ctl'));
    }
}
