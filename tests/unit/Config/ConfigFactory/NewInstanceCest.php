<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Test\Unit\Config\ConfigFactory;

use Phalcon\Config\Adapter\Ini;
use Phalcon\Config\Adapter\Json;
use Phalcon\Config\Adapter\Php;
use Phalcon\Config\Adapter\Yaml;
use Phalcon\Config\ConfigFactory;
use UnitTester;

use function dataDir;

class NewInstanceCest
{
    /**
     * Tests Phalcon\Logger\LoggerFactory :: newInstance()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-05-03
     */
    public function configFactoryNewInstance(UnitTester $I)
    {
        $I->wantToTest('Config\ConfigFactory - newInstance()');

        $factory = new ConfigFactory();
        $config  = $factory->newInstance(
            'ini',
            dataDir('fixtures/Config/config.ini')
        );

        $I->assertInstanceOf(Ini::class, $config);

        $config = $factory->newInstance(
            'json',
            dataDir('fixtures/Config/config.json')
        );

        $I->assertInstanceOf(Json::class, $config);

        $config = $factory->newInstance(
            'php',
            dataDir('fixtures/Config/config.php')
        );

        $I->assertInstanceOf(Php::class, $config);

        $config = $factory->newInstance(
            'yaml',
            dataDir('fixtures/Config/config.yml')
        );

        $I->assertInstanceOf(Yaml::class, $config);
    }
}
