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

namespace Phalcon\Tests\Unit\Assets\Collection;

use Phalcon\Assets\Collection;
use UnitTester;

/**
 * Class ValidCest
 *
 * @package Phalcon\Tests\Unit\Assets\Collection
 */
class ValidCest
{
    /**
     * Tests Phalcon\Assets\Collection :: valid()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function assetsCollectionValid(UnitTester $I)
    {
        $I->wantToTest('Assets\Collection - valid()');

        $collection = new Collection();

        $I->assertFalse($collection->valid());
        $collection->addCss('css/docs.css');

        //Position need to set to 0 by default ?
        $collection->rewind();
        $I->assertTrue($collection->valid());
    }
}
