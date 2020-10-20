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
use Phalcon\Escaper\Escaper;
use UnitTester;

/**
 * Class RemoveCest
 *
 * @package Phalcon\Tests\Unit\Di
 */
class RemoveCest
{
    /**
     * Tests Phalcon\Di :: remove()
     *
     * @param  UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-09-09
     */
    public function diRemove(UnitTester $I)
    {
        $I->wantToTest('Di - remove()');

        $container = new Di();

        $container->set('escaper', Escaper::class);

        $actual = $container->has('escaper');
        $I->assertTrue($actual);

        $container->remove('escaper');

        $actual = $container->has('escaper');
        $I->assertFalse($actual);
    }
}
