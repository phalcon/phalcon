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

namespace Phalcon\Tests\Unit\Config\Config;

use Phalcon\Config\Config;
use Phalcon\Config\Exception;
use Phalcon\Tests\Fixtures\Traits\ConfigTrait;
use Phalcon\Tests\AbstractUnitTestCase;

final class MergeTest extends AbstractUnitTestCase
{
    use ConfigTrait;

    /**
     * @return array<array-key, array<string, mixed>>
     */
    public static function getExamples(): array
    {
        return [
            [
                'source'   => [
                    'a' => 'aaa',
                    'b' => [
                        'bar' => 'rrr',
                        'baz' => 'zzz',
                    ],
                ],
                'target'   => [
                    'c' => 'ccc',
                    'b' => [
                        'baz' => 'xxx',
                    ],
                ],
                'expected' => [
                    'a' => 'aaa',
                    'b' => [
                        'bar' => 'rrr',
                        'baz' => 'xxx',
                    ],
                    'c' => 'ccc',
                ],
            ],
            [
                'source'   => [
                    '1' => 'aaa',
                    '2' => [
                        '11' => 'rrr',
                        '12' => 'zzz',
                    ],
                ],
                'target'   => [
                    '3' => 'ccc',
                    '2' => [
                        '12' => 'xxx',
                    ],
                ],
                'expected' => [
                    '1' => 'aaa',
                    '2' => [
                        '11' => 'rrr',
                        '12' => 'xxx',
                    ],
                    '3' => 'ccc',
                ],
            ],
            [
                'source'   => [
                    '0.4' => 0.4,
                ],
                'target'   => [
                    '1.1'                => 1,
                    '1.2'                => 1.2,
                    '2.8610229492188E-6' => 3,
                ],
                'expected' => [
                    '0.4'                => 0.4,
                    '1.1'                => 1,
                    '1.2'                => 1.2,
                    '2.8610229492188E-6' => 3,
                ],
            ],
            [
                'source'   => [
                    1 => 'Apple',
                ],
                'target'   => [
                    2 => 'Banana',
                ],
                'expected' => [
                    1 => 'Apple',
                    2 => 'Banana',
                ],
            ],
            [
                'source'   => [
                    0 => 'Apple',
                ],
                'target'   => [
                    2 => 'Banana',
                ],
                'expected' => [
                    0 => 'Apple',
                    2 => 'Banana',
                ],
            ],
            [
                'source'   => [
                    1   => 'Apple',
                    'p' => 'Pineapple',
                ],
                'target'   => [
                    2 => 'Banana',
                ],
                'expected' => [
                    1   => 'Apple',
                    'p' => 'Pineapple',
                    2   => 'Banana',
                ],
            ],
            [
                'source'   => [
                    'One' => [
                        1   => 'Apple',
                        'p' => 'Pineapple',
                    ],
                    'Two' => [
                        1 => 'Apple',
                    ],
                ],
                'target'   => [
                    'One' => [
                        2 => 'Banana',
                    ],
                    'Two' => [
                        2 => 'Banana',
                    ],
                ],
                'expected' => [
                    'One' => [
                        1   => 'Apple',
                        'p' => 'Pineapple',
                        2   => 'Banana',
                    ],
                    'Two' => [
                        1 => 'Apple',
                        2 => 'Banana',
                    ],
                ],
            ],
            [
                'source'   => [
                    'controllersDir' => '../x/y/z',
                    'modelsDir'      => '../x/y/z',
                    'database'       => [
                        'adapter'      => 'Mysql',
                        'host'         => 'localhost',
                        'username'     => 'scott',
                        'password'     => 'cheetah',
                        'name'         => 'test_db',
                        'charset'      => [
                            'primary' => 'utf8',
                        ],
                        'alternatives' => [
                            'primary' => 'latin1',
                            'second'  => 'latin1',
                        ],
                    ],
                ],
                'target'   => [
                    'modelsDir' => '../x/y/z',
                    'database'  => [
                        'adapter'      => 'Postgresql',
                        'host'         => 'localhost',
                        'username'     => 'peter',
                        'options'      => [
                            'case'     => 'lower',
                            'encoding' => 'SET NAMES utf8',
                        ],
                        'alternatives' => [
                            'primary' => 'swedish',
                            'third'   => 'american',
                        ],
                    ],
                ],
                'expected' => [
                    'controllersDir' => '../x/y/z',
                    'modelsDir'      => '../x/y/z',
                    'database'       => [
                        'adapter'      => 'Postgresql',
                        'host'         => 'localhost',
                        'username'     => 'peter',
                        'password'     => 'cheetah',
                        'name'         => 'test_db',
                        'charset'      => [
                            'primary' => 'utf8',
                        ],
                        'alternatives' => [
                            'primary' => 'swedish',
                            'second'  => 'latin1',
                            'third'   => 'american',
                        ],
                        'options'      => [
                            'case'     => 'lower',
                            'encoding' => 'SET NAMES utf8',
                        ],
                    ],
                ],
            ],
            [
                'source'   => [
                    'keys' => [
                        '0' => 'scott',
                        '1' => 'cheetah',
                    ],
                ],
                'target'   => [
                    'keys' => ['peter'],
                ],
                'expected' => [
                    'keys' => [
                        '0' => 'peter',
                        '1' => 'cheetah',
                    ],
                ],
            ],
            [
                'source'   => [
                    'keys' => [
                        'peter',
                    ],
                ],
                'target'   => [
                    'keys' => [
                        'cheetah',
                        'scott',
                    ],
                ],
                'expected' => [
                    'keys' => [
                        '0' => 'cheetah',
                        '1' => 'scott',
                    ],
                ],
            ],
            [
                'source'   => [
                    'test'     => 123,
                    'empty'    => [],
                    'nonEmpty' => [
                        5 => 'test',
                    ],
                ],
                'target'   => [
                    'empty' => [
                        3 => 'toEmpty',
                    ],
                ],
                'expected' => [
                    'test'     => 123,
                    'empty'    => [
                        3 => 'toEmpty',
                    ],
                    'nonEmpty' => [
                        5 => 'test',
                    ],
                ],
            ],
            [
                'source'   => [
                    'test'     => 123,
                    'empty'    => [],
                    'nonEmpty' => [
                        5 => 'test',
                    ],
                ],
                'target'   => [
                    'nonEmpty' => [
                        3 => 'toNonEmpty',
                    ],
                ],
                'expected' => [
                    'test'     => 123,
                    'empty'    => [],
                    'nonEmpty' => [
                        5 => 'test',
                        3 => 'toNonEmpty',
                    ],
                ],
            ],
        ];
    }

    /**
     * Tests Phalcon\Config\Config :: merge()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-15
     */
    public function testConfigMergeConfig(): void
    {
        $config = $this->getConfig();

        $expected = $this->getMergedByConfig();
        $actual   = $config;
        $this->assertEquals($expected, $actual);
    }

    /**
     * Tests Phalcon\Config\Config :: merge()
     *
     * @dataProvider getExamples
     *
     * @return void
     * @link         https://github.com/phalcon/cphalcon/issues/13351
     * @link         https://github.com/phalcon/cphalcon/issues/13201
     * @link         https://github.com/phalcon/cphalcon/issues/13768
     * @link         https://github.com/phalcon/cphalcon/issues/12779
     * @link         https://github.com/phalcon/phalcon/issues/196
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2121-10-21
     */
    public function testConfigMergeConfigCases(
        array $source,
        array $target,
        array $expected
    ): void {
        $source = new Config($source);
        $target = new Config($target);

        /**
         * As Config object
         */
        $actual = $source->merge($target)
                         ->toArray()
        ;
        $this->assertSame($expected, $actual);

        /**
         * As array
         */
        $actual = $source->merge($target)
                         ->toArray()
        ;
        $this->assertSame($expected, $actual);
    }

    /**
     * Merges the reference config object into an empty config object.
     *
     * @return Config
     * @throws Exception
     */
    private function getMergedByConfig(): Config
    {
        $config = new Config();
        $config->merge($this->getConfig());

        return $config;
    }
}
