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

namespace Phalcon\Tests\Unit\Flash\Direct;

use Phalcon\Flash\Direct;
use UnitTester;

/**
 * Class GetSetCssClassesCest
 *
 * @package Phalcon\Tests\Unit\Flash\Direct
 */
class GetSetCssIconClassesCest
{
    /**
     * Tests Phalcon\Flash\Direct :: getCssIconClasses()/setCssIconClasses()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function flashDirectGetSetCssIconClasses(UnitTester $I)
    {
        $I->wantToTest('Flash\Direct - getCssIconClasses()/setCssIconClasses()');

        $flash = new Direct();

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
    }
}
