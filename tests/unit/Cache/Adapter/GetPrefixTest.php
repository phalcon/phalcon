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

namespace Phalcon\Tests\Unit\Cache\Adapter;

use Codeception\Example;
use Phalcon\Tests\UnitTestCase;
use Phalcon\Cache\Adapter\Apcu;
use Phalcon\Cache\Adapter\Libmemcached;
use Phalcon\Cache\Adapter\Memory;
use Phalcon\Cache\Adapter\Redis;
use Phalcon\Cache\Adapter\Stream;
use Phalcon\Cache\Adapter\Weak;
use Phalcon\Storage\SerializerFactory;

use function array_merge;
use function getOptionsRedis;
use function outputDir;
use function sprintf;

final class GetPrefixTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Cache\Adapter\* :: getPrefix()
     *
     * @dataProvider getExamples
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-09-09
     */
    public function testCacheAdapterGetSetPrefix(
        string $class,
        array $options,
        string $expected,
        string $extension
    ): void {
        if (!empty($extension)) {
            $this->checkExtensionIsLoaded($extension);
        }

        $serializer = new SerializerFactory();
        $adapter    = new $class($serializer, $options);

        $actual = $adapter->getPrefix();
        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array[]
     */
    public static function getExamples(): array
    {
        return [
            [
                Apcu::class,
                [
                ],
                'ph-apcu-',
                'apcu',
            ],
            [
                Apcu::class,
                [
                    'prefix' => '',
                ],
                '',
                'apcu',
            ],
            [
                Apcu::class,
                [
                    'prefix' => 'my-prefix',
                ],
                'my-prefix',
                'apcu',
            ],
            [
                Libmemcached::class,
                array_merge(
                    getOptionsLibmemcached(),
                    [
                    ]
                ),
                'ph-memc-',
                'memcached',
            ],
            [
                Libmemcached::class,
                array_merge(
                    getOptionsLibmemcached(),
                    [
                        'prefix' => '',
                    ]
                ),
                '',
                'memcached',
            ],
            [
                Libmemcached::class,
                array_merge(
                    getOptionsLibmemcached(),
                    [
                        'prefix' => 'my-prefix',
                    ]
                ),
                'my-prefix',
                'memcached',
            ],
            [
                Memory::class,
                [
                ],
                'ph-memo-',
                '',
            ],
            [
                Memory::class,
                [
                    'prefix' => '',
                ],
                '',
                '',
            ],
            [
                Memory::class,
                [
                    'prefix' => 'my-prefix',
                ],
                'my-prefix',
                '',
            ],
            [
                Redis::class,
                array_merge(
                    getOptionsRedis(),
                    [
                    ]
                ),
                'ph-reds-',
                'redis',
            ],
            [
                Redis::class,
                array_merge(
                    getOptionsRedis(),
                    [
                        'prefix' => '',
                    ]
                ),
                '',
                'redis',
            ],
            [
                Redis::class,
                array_merge(
                    getOptionsRedis(),
                    [
                        'prefix' => 'my-prefix',
                    ]
                ),
                'my-prefix',
                'redis',
            ],
            [
                Stream::class,
                [
                    'storageDir' => outputDir(),
                ],
                'ph-strm',
                '',
            ],
            [
                Stream::class,
                [
                    'storageDir' => outputDir(),
                    'prefix'     => '',
                ],
                '',
                '',
            ],
            [
                Stream::class,
                [
                    'storageDir' => outputDir(),
                    'prefix'     => 'my-prefix',
                ],
                'my-prefix',
                '',
            ],
            [
                Weak::class,
                [
                ],
                '',
                '',
            ],
            [
                Weak::class,
                [
                    'prefix' => '',
                ],
                '',
                '',
            ],
            [
                Weak::class,
                [
                    'prefix' => 'my-prefix',
                ],
                '',
                '',
            ],
        ];
    }
}
