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

namespace Phalcon\Tests\Unit\Logger\Adapter\Stream;

use Phalcon\Logger\Adapter\Stream;
use Phalcon\Logger\Formatter\FormatterInterface;
use UnitTester;

class GetFormatterCest
{
    /**
     * Tests Phalcon\Logger\Adapter\Stream :: getFormatter()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function loggerAdapterStreamGetFormatter(UnitTester $I)
    {
        $I->wantToTest('Logger\Adapter\Stream - getFormatter()');

        $fileName = $I->getNewFileName('log', 'log');
        $fileName = logsDir($fileName);

        $adapter = new Stream($fileName);

        $I->assertInstanceOf(
            FormatterInterface::class,
            $adapter->getFormatter()
        );

        $adapter->close();
        $I->safeDeleteFile($fileName);
    }
}
