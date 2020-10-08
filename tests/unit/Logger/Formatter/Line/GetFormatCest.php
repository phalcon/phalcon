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

namespace Phalcon\Tests\Unit\Logger\Formatter\Line;

use Phalcon\Logger\Formatter\Line;
use UnitTester;

class GetFormatCest
{
    /**
     * Tests Phalcon\Logger\Formatter\Line :: getFormat()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function loggerFormatterLineGetFormat(UnitTester $I)
    {
        $I->wantToTest('Logger\Formatter\Line - getFormat()');

        $formatter = new Line();

        $I->assertEquals(
            '[{date}][{type}] {message}',
            $formatter->getFormat()
        );
    }
}
