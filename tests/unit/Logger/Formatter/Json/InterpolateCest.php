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

use DateTimeImmutable;
use DateTimeZone;
use Phalcon\Logger\Formatter\Json;
use Phalcon\Logger\Item;
use Phalcon\Logger\Logger;
use UnitTester;

use function date_default_timezone_get;

class InterpolateCest
{
    /**
     * Tests Phalcon\Logger\Formatter\Json :: interpolate()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function loggerFormatterJsonInterpolate(UnitTester $I)
    {
        $I->wantToTest('Logger\Formatter\Json - interpolate()');

        $formatter = new Json();

        $I->assertEquals(
            'The sky is blue',
            $formatter->interpolate(
                'The sky is {color}',
                [
                    'color' => 'blue',
                ]
            )
        );
    }

    /**
     * Tests Phalcon\Logger\Formatter\Json :: interpolate() - format
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function loggerFormatterJsonInterpolateFormat(UnitTester $I)
    {
        $I->wantToTest('Logger\Formatter\Json - interpolate() - format()');

        $formatter = new Json();
        $message   = 'The sky is {color}';
        $context   = [
            'color' => 'blue',
        ];

        $timezone = date_default_timezone_get();
        $datetime = new DateTimeImmutable('now', new DateTimeZone($timezone));
        $item     = new Item(
            $message,
            'debug',
            Logger::DEBUG,
            $datetime,
            $context
        );

        $expected = sprintf(
            '{"type":"debug","message":"The sky is blue","timestamp":"%s"}',
            $datetime->format('c')
        );

        $I->assertEquals(
            $expected,
            $formatter->format($item)
        );
    }
}
