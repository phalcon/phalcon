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

namespace Phalcon\Tests\Unit\Collection\Collection;

use Phalcon\Collection\Collection;
use UnitTester;

class ConstructCest
{
    /**
     * Tests Phalcon\Collection :: __construct()
     *
     * @param UnitTester $I
     *
     * @since  2020-09-09
     *
     * @author Phalcon Team <team@phalcon.io>
     */
    public function collectionConstruct(UnitTester $I)
    {
        $I->wantToTest('Collection - __construct()');

        $collection = new Collection();

        $I->assertInstanceOf(
            Collection::class,
            $collection
        );
    }
}
