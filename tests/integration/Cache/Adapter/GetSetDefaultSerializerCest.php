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

namespace Phalcon\Tests\Integration\Cache\Adapter;

use Codeception\Example;
use IntegrationTester;
use Phalcon\Cache\Adapter\Apcu;
use Phalcon\Cache\Adapter\Libmemcached;
use Phalcon\Cache\Adapter\Memory;
use Phalcon\Cache\Adapter\Redis;
use Phalcon\Cache\Adapter\Stream;
use Phalcon\Storage\SerializerFactory;

use function getOptionsLibmemcached;
use function getOptionsRedis;
use function outputDir;

class GetSetDefaultSerializerCest
{
    /**
     * Tests Phalcon\Cache\Adapter\* ::
     * getDefaultSerializer()/setDefaultSerializer()
     *
     * @dataProvider getExamples
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-09-09
     */
    public function storageAdapterGetSetDefaultSerializer(IntegrationTester $I, Example $example)
    {
        $I->wantToTest(
            sprintf(
                'Cache\Adapter\%s - getDefaultSerializer()/setDefaultSerializer()',
                $example['className']
            )
        );

        $extension = $example['extension'];
        $class     = $example['class'];
        $options   = $example['options'];

        if (!empty($extension)) {
            $I->checkExtensionIsLoaded($extension);
        }

        $serializer = new SerializerFactory();
        $adapter    = new $class($serializer, $options);

        $expected = 'php';
        $actual   = $adapter->getDefaultSerializer();
        $I->assertEquals($expected, $actual);

        $adapter->setDefaultSerializer('Base64');
        $expected = 'base64';
        $actual   = $adapter->getDefaultSerializer();
        $I->assertEquals($expected, $actual);
    }

    /**
     * @return array[]
     */
    private function getExamples(): array
    {
        return [
            [
                'className' => 'Apcu',
                'class'     => Apcu::class,
                'options'   => [],
                'extension' => 'apcu',
            ],
            [
                'className' => 'Libmemcached',
                'class'     => Libmemcached::class,
                'options'   => getOptionsLibmemcached(),
                'extension' => 'memcached',
            ],
            [
                'className' => 'Memory',
                'label'     => 'default',
                'class'     => Memory::class,
                'options'   => [],
                'extension' => '',
            ],
            [
                'className' => 'Redis',
                'label'     => 'default',
                'class'     => Redis::class,
                'options'   => getOptionsRedis(),
                'extension' => 'redis',
            ],
            [
                'className' => 'Stream',
                'label'     => 'default',
                'class'     => Stream::class,
                'options'   => [
                    'storageDir' => outputDir(),
                ],
                'extension' => '',
            ],
        ];
    }
}
