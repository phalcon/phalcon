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

class SetDateFormatCest
{
    /**
     * Tests Phalcon\Logger\Formatter\Json :: setDateFormat()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function loggerFormatterJsonSetDateFormat(UnitTester $I)
    {
        $I->wantToTest('Logger\Formatter\Json - setDateFormat()');

        $formatter = new Json();

        $format = 'YmdHis';

        $formatter->setDateFormat($format);

        $I->assertEquals(
            $format,
            $formatter->getDateFormat()
        );
    }
}
