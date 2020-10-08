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

namespace Phalcon\Tests\Unit\Logger\Formatter\Json;

use Phalcon\Logger\Formatter\Json;
use UnitTester;

class GetDateFormatCest
{
    /**
     * Tests Phalcon\Logger\Formatter\Json :: getDateFormat()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function loggerFormatterJsonGetDateFormat(UnitTester $I)
    {
        $I->wantToTest('Logger\Formatter\Json - getDateFormat()');

        $formatter = new Json();

        $I->assertEquals(
            'c',
            $formatter->getDateFormat()
        );
    }
}
