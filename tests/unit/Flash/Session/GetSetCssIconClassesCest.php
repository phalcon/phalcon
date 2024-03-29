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

namespace Phalcon\Tests\Unit\Flash\Session;

use Phalcon\Flash\Session;
use Phalcon\Tests\Fixtures\Traits\DiTrait;
use UnitTester;

/**
 * Class GetSetCssClassesCest
 *
 * @package Phalcon\Tests\Unit\Flash\Session
 */
class GetSetCssIconClassesCest
{
    use DiTrait;

    public function _before(UnitTester $I)
    {
        $this->setNewFactoryDefault();
        $this->setDiService('sessionStream');
    }

    /**
     * Tests Phalcon\Flash\Session :: getCssIconClasses()/setCssIconClasses()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function flashSessionGetSetCssIconClasses(UnitTester $I)
    {
        $I->wantToTest('Flash\Session - getCssIconClasses()/setCssIconClasses()');

        $session = $this->container->getShared('session');
        $session->start();

        $flash = new Session();
        $flash->setDI($this->container);

        $expected   = [
            'error'   => 'errorMessage',
            'success' => 'successMessage',
            'notice'  => 'noticeMessage',
            'warning' => 'warningMessage',
        ];
        $newClasses = [
            'error'   => 'alert alert-error',
            'success' => 'alert alert-success',
            'notice'  => 'alert alert-notice',
            'warning' => 'alert alert-warning',
        ];

        $actual = $flash->getCssIconClasses();
        $I->assertEmpty($actual);

        $flash->setCssIconClasses($newClasses);

        $expected = $newClasses;
        $actual   = $flash->getCssIconClasses();
        $I->assertSame($expected, $actual);

        $session->destroy();
    }
}
