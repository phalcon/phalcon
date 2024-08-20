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

namespace Phalcon\Tests\Unit\Config\Adapter\Yaml;

use Phalcon\Config\Adapter\Yaml;
use Phalcon\Config\Exception;
use Phalcon\Tests\Fixtures\Config\Adapter\YamlExtensionLoadedFixture;
use Phalcon\Tests\Fixtures\Config\Adapter\YamlParseFileFixture;
use Phalcon\Tests\Fixtures\Traits\ConfigTrait;
use Phalcon\Tests\AbstractUnitTestCase;

use function basename;
use function dataDir;
use function hash;

use const PATH_DATA;

final class ConstructTest extends AbstractUnitTestCase
{
    use ConfigTrait;

    /**
     * Tests Phalcon\Config\Adapter\Yaml :: __construct() - callbacks
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2021-10-21
     */
    public function testConfigAdapterYamlConstructCallbacks(): void
    {
        $config = new Yaml(
            dataDir('assets/config/callbacks.yml'),
            [
                '!decrypt' => function ($value) {
                    return hash('sha256', $value);
                },
                '!approot' => function ($value) {
                    return PATH_DATA . $value;
                },
            ]
        );

        $expected = PATH_DATA . '/app/controllers/';
        $actual   = $config->application->controllersDir;
        $this->assertSame($expected, $actual);

        $expected = '9f7030891b235f3e06c4bff74ae9dc1b9b59d4f2e4e6fd94eeb2b91caee5d223';
        $actual   = $config->database->password;
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Config\Adapter\Yaml :: __construct() - exceptions
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2021-10-21
     */
    public function testConfigAdapterYamlConstructExceptionLoaded(): void
    {
        $filePath = dataDir('assets/config/callbacks.yml');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Yaml extension is not loaded');

        (new YamlExtensionLoadedFixture($filePath));
    }

    /**
     * Tests Phalcon\Config\Adapter\Yaml :: __construct() - exceptions
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2021-10-21
     */
    public function testConfigAdapterYamlConstructExceptionParseFile(): void
    {
        $filePath = dataDir('assets/config/callbacks.yml');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            'Configuration file ' . basename($filePath) . ' can\'t be loaded'
        );

        (new YamlParseFileFixture($filePath));
    }
}
