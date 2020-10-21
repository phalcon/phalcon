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

namespace Phalcon\Tests\Integration\Session\Bag;

use IntegrationTester;
use Phalcon\Di\FactoryDefault;
use Phalcon\Session\Bag;
use Phalcon\Tests\Fixtures\Traits\DiTrait;

/**
 * Class GetSetDICest
 *
 * @package Phalcon\Tests\Integration\Session\Bag
 */
class GetSetDICest
{
    use DiTrait;

    /**
     * Tests Phalcon\Session\Bag :: getDI()/setDI()
     *
     * @param IntegrationTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function sessionBagGetSetDI(IntegrationTester $I)
    {
        $I->wantToTest("Session\Bag - getDI()/setDI()");

        $this->setNewFactoryDefault();
        $this->setDiService('sessionStream');

        $session   = new Bag('DiTest');
        $container = new FactoryDefault();

        $session->setDI($container);

        $I->assertEquals($container, $session->getDI());
    }
}
