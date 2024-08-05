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

use Phalcon\Support\Debug;
use Phalcon\Support\Version;
use Phalcon\Tests\UnitTestCase;

final class GetVersionTest extends UnitTestCase
{
//    use DiTrait;

    /**
     * @return void
     */
    public function setUp(): void
    {
//        $this->setNewFactoryDefault();
    }

    /**
     * Tests Phalcon\Debug :: getVersion()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testSupportDebugGetVersion(): void
    {
        $debug   = new Debug();
        $version = new Version();

        $target  = '"_new"';
        $uri     = '"https://docs.phalcon.io/'
            . $version->getPart(Version::VERSION_MAJOR) . '.'
            . $version->getPart(Version::VERSION_MEDIUM) . '/en/"';
        $version = $version->get();

        $this->assertSame(
            "<div class=\"version\">Phalcon Framework <a href={$uri} target={$target}>{$version}</a></div>",
            $debug->getVersion()
        );
    }
}
