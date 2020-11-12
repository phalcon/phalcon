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

use Phalcon\Support\Debug\Debug;
use Phalcon\Tests\Fixtures\Traits\DiTrait;
use Phalcon\Version\Version;
use UnitTester;

/**
 * Class GetVersionCest
 *
 * @package Phalcon\Tests\Unit\Support\Debug
 */
class GetVersionCest
{
    use DiTrait;

    /**
     * @param UnitTester $I
     */
    public function _before(UnitTester $I)
    {
        $this->setNewFactoryDefault();
    }

    /**
     * Tests Phalcon\Debug :: getVersion()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function debugGetVersion(UnitTester $I)
    {
        $I->wantToTest('Debug - getVersion()');

        $debug = new Debug();

        $uri     = 'https://docs.phalcon.io/'
            . Version::getPart(Version::VERSION_MAJOR) . '.'
            . Version::getPart(Version::VERSION_MEDIUM) . '/en/';
        $version = Version::get();


        $expected = '<div class="version">Phalcon Framework '
            . '<a href="' . $uri . '" target="_new">' . $version . '</a></div>';
        $actual   = $debug->getVersion();

        $I->assertEquals($expected, $actual);
    }
}
