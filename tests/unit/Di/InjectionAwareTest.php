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

namespace Phalcon\Tests\Unit\Di;

use Phalcon\Di\Di;
use Phalcon\Tests\Fixtures\Di\InjectionAwareComponent;
use Phalcon\Tests\AbstractUnitTestCase;

use function spl_object_hash;

class InjectionAwareTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Di\Di :: injection aware trait
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-09-09
     */
    public function testDiInjectionAware(): void
    {
        $container = new Di();
        $component = new InjectionAwareComponent();

        $component->setDI($container);
        $actual = $component->getDI();

        $expected = spl_object_hash($container);
        $actual   = spl_object_hash($actual);
        $this->assertSame($expected, $actual);
    }
}
