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

namespace Phalcon\Tests\Unit\Mvc\View\Engine\Volt;

use Phalcon\Di\Di;
use Phalcon\Mvc\View;
use Phalcon\Mvc\View\Engine\Volt;
use Phalcon\Tests\UnitTestCase;

class GetSetDITest extends UnitTestCase
{
    /**
     * Tests Phalcon\Mvc\View\Engine\Volt :: getDI() / setDI()
     *
     * @author Sid Roberts <https://github.com/SidRoberts>
     * @since  2019-05-22
     */
    public function testMvcViewEngineVoltGetSetDI(): void
    {
        $view = new View();

        $di1 = new Di();
        $di2 = new Di();

        $engine = new Volt($view, $di1);

        $this->assertSame(
            $di1,
            $engine->getDI()
        );

        $engine->setDI($di2);

        $this->assertSame(
            $di2,
            $engine->getDI()
        );
    }
}
