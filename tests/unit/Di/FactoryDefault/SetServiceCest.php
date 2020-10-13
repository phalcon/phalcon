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

namespace Phalcon\Tests\Unit\Di\FactoryDefault;

use Phalcon\Di\FactoryDefault;
use Phalcon\Di\Service;
use Phalcon\Escaper\Escaper;
use UnitTester;

/**
 * Class SetServiceCest
 *
 * @package Phalcon\Tests\Unit\Di\FactoryDefault
 */
class SetServiceCest
{
    /**
     * Unit Tests Phalcon\Di\FactoryDefault :: setService()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-09-09
     */
    public function diFactoryDefaultSetRaw(UnitTester $I)
    {
        $I->wantToTest('Di\FactoryDefault - setService()');

        $container = new FactoryDefault();

        $expected = new Service(Escaper::class);
        $actual   = $container->setService('escaper', $expected);
        $I->assertSame($expected, $actual);
    }
}
