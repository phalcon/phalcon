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

namespace Phalcon\Tests\Unit\Session\Manager;

use Phalcon\Tests\UnitTestCase;
use Phalcon\Di\FactoryDefault;
use Phalcon\Session\Manager;

final class GetSetDITest extends UnitTestCase
{
    /**
     * Tests Phalcon\Session\Manager :: getDI()/setDI()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testSessionManagerGetSetDI(): void
    {
        $session   = new Manager();
        $container = new FactoryDefault();

        $session->setDI($container);

        $actual = $session->getDI();
        $this->assertEquals($container, $actual);
    }
}
