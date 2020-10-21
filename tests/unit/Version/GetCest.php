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

namespace Phalcon\Tests\Unit\Version;

use Codeception\Stub;
use Phalcon\Tests\Fixtures\Traits\VersionTrait;
use Phalcon\Tests\Fixtures\VersionFixture;
use Phalcon\Version\Version;
use UnitTester;

use function is_string;

/**
 * Class GetCest
 *
 * @package Phalcon\Tests\Unit\Version
 */
class GetCest
{
    use VersionTrait;

    /**
     * Tests Phalcon\Version :: get()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function versionGet(UnitTester $I)
    {
        $I->wantToTest('Version - get()');

        $actual = is_string(Version::get());
        $I->assertTrue($actual);
    }

    /**
     * Tests the getId() translation to get()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function versionGetIdToGet(UnitTester $I)
    {
        $I->wantToTest('Version - getId() to get()');

        $id = Version::getId();

        $major     = intval($id[0]);
        $med       = intval($id[1] . $id[2]);
        $min       = intval($id[3] . $id[4]);
        $special   = $this->numberToSpecial($id[5]);
        $specialNo = ($special) ? $id[6] : '';
        $expected  = "{$major}.{$med}.{$min}";
        if (true !== empty($special)) {
            $expected .= "-{$special}";
            if (true !== empty($specialNo)) {
                $expected .= ".{$specialNo}";
            }
        }

        $expected = trim($expected);
        $actual   = Version::get();
        $I->assertEquals($expected, $actual);
    }

    /**
     * Tests the get with a special version
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function versionGetWithSpecialVersion(UnitTester $I)
    {
        $I->wantToTest('Version - get() with special version');

        $expected = '5.0.0-alpha.1';
        $actual   = VersionFixture::get();
        $I->assertEquals($expected, $actual);
    }
}
