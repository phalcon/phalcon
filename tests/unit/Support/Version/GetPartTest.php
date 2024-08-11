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

namespace Phalcon\Tests\Unit\Support\Version;

use Phalcon\Support\Version;
use Phalcon\Tests\Fixtures\Traits\VersionTrait;
use Phalcon\Tests\AbstractUnitTestCase;

final class GetPartTest extends AbstractUnitTestCase
{
    use VersionTrait;

    /**
     * Tests Phalcon\Support\Version :: getPart()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testSupportVersionGetPart(): void
    {
        /*
         * Note: getId() returns a version string in the format ABBCCDE
         * where A is the major version, BB is the medium version (2 digits)
         * CC is the minor version (2 digits), D is the release type (see
         * Phalcon\Support\Version) and E is the release number (for example 2 for RC2)
         */

        $version = new Version();
        $id      = $version->getId();

        // The major version is the first digit
        $expected = (string)$id[0];
        $actual   = $version->getPart(Version::VERSION_MAJOR);
        $this->assertSame($expected, $actual);

        // The medium version is the second and third digits
        // This is int to string because we might end up with "00"
        $expected = (string)intval($id[1] . $id[2]);
        $actual   = $version->getPart(Version::VERSION_MEDIUM);
        $this->assertSame($expected, $actual);

        // The minor version is the fourth and fifth digits
        $expected = (string)intval($id[3] . $id[4]);
        $actual   = $version->getPart(Version::VERSION_MINOR);
        $this->assertSame($expected, $actual);

        $expected = $this->numberToSpecial((string)$id[5]);
        $actual   = $version->getPart(Version::VERSION_SPECIAL);
        $this->assertSame($expected, $actual);

        $special  = $this->numberToSpecial((string)$id[6]);
        $expected = (string)(($special) ? $id[6] : 0);
        $actual   = $version->getPart(Version::VERSION_SPECIAL_NUMBER);
        $this->assertSame($expected, $actual);

        $expected = $version->get();
        $actual   = $version->getPart(7);
        $this->assertSame($expected, $actual);
    }
}
