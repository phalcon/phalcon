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

namespace Phalcon\Tests\Unit\Di\Injectable;

use Phalcon\Di\Di;
use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;
use Phalcon\Tests\Unit\Di\Fake\FakeInjectableRedeclared;

final class RedeclaredPropertiesTest extends AbstractUnitTestCase
{
    /**
     * A component can declare its container with no type, as in cphalcon.
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     *
     * @issue  https://github.com/phalcon/phalcon/issues/840
     */
    public function testDiInjectableRedeclaredContainer(): void
    {
        $container = new Di();
        $component = new FakeInjectableRedeclared();

        $component->setDI($container);

        $this->assertSame($container, $component->getDI());
    }
}
