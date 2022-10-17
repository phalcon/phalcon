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

namespace Phalcon\Tests\Cli\Cli\Dispatcher;

use CliTester;
use Phalcon\Cli\Dispatcher;

/**
 * Class HasOptionCest
 */
class HasOptionCest
{
    /**
     * Tests Phalcon\Cli\Dispatcher :: hasOption()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function cliDispatcherHasOption(CliTester $I)
    {
        $I->wantToTest('Cli\Dispatcher - hasOption()');
        $dispatcher = new Dispatcher();
        $optionName = "Phalcon";

        $actual = $dispatcher->hasOption($optionName);
        $I->assertFalse($actual);

        $dispatcher->setOptions([$optionName => "value"]);

        $actual = $dispatcher->hasOption($optionName);
        $I->assertTrue($actual);

        // Options should be case-sensitive
        $actual = $dispatcher->hasOption(strtolower($optionName));
        $I->assertFalse($actual);
    }
}
