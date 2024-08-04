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

namespace Phalcon\Tests\Unit\Config\Adapter\Ini;

use Phalcon\Config\Adapter\Ini;
use Phalcon\Config\Exception;
use Phalcon\Tests\Fixtures\Config\Adapter\IniParseFileFixture;
use Phalcon\Tests\Fixtures\Traits\ConfigTrait;
use Phalcon\Tests\UnitTestCase;

use function supportDir;

final class ConstructTest extends UnitTestCase
{
    use ConfigTrait;

    /**
     * Tests Phalcon\Config\Adapter\Ini :: __construct()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function testConfigAdapterIniConstruct(): void
    {
        $this->config['database']['num1'] = false;
        $this->config['database']['num2'] = false;
        $this->config['database']['num3'] = false;
        $this->config['database']['num4'] = true;
        $this->config['database']['num5'] = true;
        $this->config['database']['num6'] = true;
        $this->config['database']['num7'] = null;
        $this->config['database']['num8'] = 123;
        $this->config['database']['num9'] = (float)123.45;

        $config = $this->getConfig('Ini');

        $this->compareConfig($this->config, $config);
    }

    /**
     * Tests Phalcon\Config\Adapter\Ini :: __construct() - constants
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function testConfigAdapterIniConstructConstants(): void
    {
        define('TEST_CONST', 'foo');

        $config = new Ini(
            dataDir('assets/config/config-with-constants.ini'),
            INI_SCANNER_NORMAL
        );

        $expected = [
            'test'    => 'foo',
            'path'    => 'foo/something/else',
            'section' => [
                'test'      => 'foo',
                'path'      => 'foo/another-thing/somewhere',
                'parent'    => [
                    'property'  => 'foo',
                    'property2' => 'foohello',
                ],
                'testArray' => [
                    'value1',
                    'value2',
                ],
            ],

        ];

        $actual = $config->toArray();
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Config\Adapter\Ini :: __construct() - exceptions
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-10-26
     */
    public function testConfigAdapterIniConstructExceptions(): void
    {
        $filePath = dataDir('assets/config/config-with-constants.ini');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            'Configuration file ' . basename($filePath) . ' cannot be loaded'
        );

        (new IniParseFileFixture($filePath));
    }
}
