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

namespace Phalcon\Tests\Unit\Config\Adapter;

use Phalcon\Tests\Fixtures\Traits\ConfigTrait;
use Phalcon\Tests\AbstractUnitTestCase;

final class PathTest extends AbstractUnitTestCase
{
    use ConfigTrait;

    /**
     * Tests Phalcon\Config\Adapter\Grouped :: path()
     *
     * @dataProvider providerConfigAdaptersNotGrouped
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2018-11-13
     */
    public function testConfigAdapterPath(
        string $adapter
    ): void {
        $config = $this->getConfig($adapter);

        $expected = 1;
        $actual   = $config->path('test');
        $this->assertCount($expected, $actual);

        $expected = 'yeah';
        $actual   = $config->path('test.parent.property2');
        $this->assertEquals($expected, $actual);
    }

    /**
     * Tests Phalcon\Config\Adapter\Grouped :: path() - default
     *
     * @dataProvider providerConfigAdapters
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2018-11-13
     */
    public function testConfigAdapterPathDefault(
        string $adapter
    ): void {
        $config = $this->getConfig($adapter);

        $expected = 'Unknown';
        $actual   = $config->path('test.parent.property3', 'Unknown');
        $this->assertEquals($expected, $actual);
    }
}
