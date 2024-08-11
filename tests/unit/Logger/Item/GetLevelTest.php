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

namespace Phalcon\Tests\Unit\Logger\Item;

use DateTimeImmutable;
use DateTimeZone;
use Phalcon\Logger\Enum;
use Phalcon\Logger\Item;
use Phalcon\Tests\AbstractUnitTestCase;

use function date_default_timezone_get;

final class GetLevelTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Logger\Item :: getLevel()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testLoggerItemGetLevel(): void
    {
        $timezone = date_default_timezone_get();
        $datetime = new DateTimeImmutable('now', new DateTimeZone($timezone));
        $item     = new Item(
            'log message',
            'debug',
            Enum::DEBUG,
            $datetime
        );

        $expected = Enum::DEBUG;
        $actual   = $item->getLevel();
        $this->assertSame($expected, $actual);
    }
}
