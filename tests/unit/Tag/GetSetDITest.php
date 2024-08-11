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

namespace Phalcon\Tests\Unit\Tag;

use Phalcon\Di\Di;
use Phalcon\Tag;
use Phalcon\Tests\AbstractUnitTestCase;

class GetSetDITest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Tag :: getDI() / setDI()
     *
     * @author Jeremy PASTOURET <https://github.com/jenovateurs>
     * @since  2019-12-07
     */
    public function testTagGetSetDI(): void
    {
        $container = new Di();

        $tag = new Tag();

        $tag->setDI($container);

        $expected = $container;
        $actual   = $tag->getDI();
        $this->assertSame($expected, $actual);

        $class  = Di::class;
        $actual = $tag->getDI();
        $this->assertInstanceOf($class, $actual);

        $expected = $container;
        $this->assertSame($expected, $actual);
    }
}
