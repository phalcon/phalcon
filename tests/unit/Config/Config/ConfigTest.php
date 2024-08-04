<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace Phalcon\Tests\Unit\Config\Config;

use Phalcon\Config\Config;
use Phalcon\Tests\Fixtures\Traits\ConfigTrait;
use Phalcon\Tests\UnitTestCase;

final class ConfigTest extends UnitTestCase
{
    use ConfigTrait;

    /**
     * Tests converting child array to config object
     *
     * @author Rian Orie <rian.orie@gmail.com>
     * @since  2014-11-12
     */
    public function testChildArrayToConfigObject(): void
    {
        $config = new Config(
            [
                'childNode' => ['A', 'B', 'C'],
            ]
        );

        $expected = Config::class;
        $actual   = $config->childNode;
        $this->assertInstanceOf($expected, $actual);

        $actual = $config->get('childNode');
        $this->assertInstanceOf($expected, $actual);

        $actual = $config->offsetGet('childNode');
        $this->assertInstanceOf($expected, $actual);
    }

    /**
     * Test insensitive in sub arrays
     *
     * @return void
     *
     * @return void
     *
     * @issue  16171
     * @author Phalcon Team <team@phalcon.io>
     * @since  2022-10-19
     */
    public function testConfigConfigConfigInsensitive(): void
    {
        $settings = [
            'database' => [
                'adapter'  => 'Mysql',
                'host'     => 'localhost',
                'username' => 'scott',
                'password' => 'cheetah',
                'name'     => 'test_db',
            ],
            'other'    => [1, 2, 3, 4],
        ];
        $config   = new Config($settings, false);

        /** @var Config $database */
        $database = $config->get('database');

        $class = Config::class;
        $this->assertInstanceOf($class, $database);

        $actual = $database->has('adapter');
        $this->assertTrue($actual);

        $actual = $database->has('ADAPTER');
        $this->assertFalse($actual);
    }

    /**
     * Tests standard config simple array
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2012-09-11
     */
    public function testStandardConfigSimpleArray(): void
    {
        $settings = [
            'database' => [
                'adapter'  => 'Mysql',
                'host'     => 'localhost',
                'username' => 'scott',
                'password' => 'cheetah',
                'name'     => 'test_db',
            ],
            'other'    => [1, 2, 3, 4],
        ];

        $expected = new Config(
            [
                'database' => [
                    'adapter'  => 'Mysql',
                    'host'     => 'localhost',
                    'username' => 'scott',
                    'password' => 'cheetah',
                    'name'     => 'test_db',
                ],
                'other'    => [
                    0 => 1,
                    1 => 2,
                    2 => 3,
                    3 => 4,
                ],
            ]
        );
        $actual   = new Config($settings);
        $this->assertEquals($expected, $actual);
    }
}
