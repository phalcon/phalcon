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

namespace Phalcon\Tests\Unit\Support\Debug;

use UnitTester;

class SetShowBackTraceCest
{
    /**
     * Tests Phalcon\Debug :: setShowBackTrace()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function supportDebugSetShowBackTrace(UnitTester $I)
    {
        $I->wantToTest('Debug - setShowBackTrace()');

        $I->skipTest('Need implementation');
    }
}
