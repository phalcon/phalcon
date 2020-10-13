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

class HasCest
{
    /**
     * Tests Phalcon\Di :: has()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function diHas(UnitTester $I)
    {
        $I->wantToTest('Di - has()');

        $container = new Di();

        $actual = $container->has('escaper');
        $I->assertFalse($actual);

        $container->set('escaper', Escaper::class);

        $actual = $container->has('escaper');
        $I->assertTrue($actual);
    }
}
