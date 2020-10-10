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

namespace Phalcon\Tests\Unit\Events\Manager;

use UnitTester;

class FireQueueCest
{
    /**
     * Tests Phalcon\Events\Manager :: fireQueue()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function eventsManagerFireQueue(UnitTester $I)
    {
        $I->wantToTest('Events\Manager - fireQueue()');

        $I->skipTest('Need implementation');
    }
}
